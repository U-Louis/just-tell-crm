<?php

namespace App\Http\Voice;

use App\Domain\Crm\Actor;
use App\Domain\Crm\Column;
use App\Domain\Crm\Contact;
use App\Domain\Crm\CreateContact;
use App\Domain\Crm\CreateProduct;
use App\Domain\Crm\CreateUser;
use App\Domain\Crm\CrmException;
use App\Domain\Crm\CrmRepository;
use App\Domain\Crm\DeleteContact;
use App\Domain\Crm\DeleteEstimate;
use App\Domain\Crm\DeleteProduct;
use App\Domain\Crm\DeleteUser;
use App\Domain\Crm\EstimateDetail;
use App\Domain\Crm\ListedEstimate;
use App\Domain\Crm\Product;
use App\Domain\Crm\QualifyContact;
use App\Domain\Crm\SendEstimate;
use App\Domain\Crm\UpdateContact;
use App\Domain\Crm\UpdateEstimate;
use App\Domain\Crm\UpdateProduct;
use App\Domain\Crm\UpdateUser;
use App\Domain\Crm\User;
use App\Domain\Crm\VoiceRun;
use App\Http\CrmLog;
use App\Infrastructure\Persistence\Crm\VoiceRunStore;
use App\Policies\CrmPolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class VoiceTools
{
    private const LIMIT = 50;

    public function __construct(
        private CrmRepository $crm,
        private VoiceRunStore $runs,
        private CrmPolicy $policy,
        private CreateContact $createContact,
        private UpdateContact $updateContact,
        private DeleteContact $deleteContact,
        private QualifyContact $qualifyContact,
        private SendEstimate $sendEstimate,
        private UpdateEstimate $updateEstimate,
        private DeleteEstimate $deleteEstimate,
        private CreateUser $createUser,
        private UpdateUser $updateUser,
        private DeleteUser $deleteUser,
        private CreateProduct $createProduct,
        private UpdateProduct $updateProduct,
        private DeleteProduct $deleteProduct,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     */
    public function call(Request $request, VoiceRun $run, string $name, array $input): JsonResponse
    {
        if (VoiceCatalog::find($name) === null) {
            return $this->error('voice.tool.unknown', 'F38', 404);
        }

        $actor = $this->crm->findUser($run->actingUserId);
        if ($actor === null) {
            return $this->error('user.required', 'F38', 422);
        }

        try {
            $payload = $this->perform($run, $actor, $name, $input);
        } catch (CrmException $exception) {
            CrmLog::write($request, $exception->featureId, 'refused', $exception->errorCode, $actor->id);

            return $this->error($exception->errorCode, $exception->featureId, 422, $exception->fields);
        } catch (\RuntimeException) {
            return $this->error('voice.not_found', 'F38', 404);
        }

        CrmLog::write($request, 'F38', 'success', null, $actor->id);

        return response()->json($payload);
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function perform(VoiceRun $run, User $actor, string $name, array $input): array
    {
        $acting = new Actor($actor->id);

        return match ($name) {
            'home' => ['columns' => $this->board($actor)],
            'acting_user_store' => $this->chooseUser($run, $input),
            'users_index' => [
                'users' => array_map(fn (User $user) => $this->userPayload($user), $this->crm->searchUsers($this->query($input), self::LIMIT, 0)),
                'count' => $this->crm->countUsers(),
            ],
            'users_show' => $this->userPayload($this->visibleUser($this->requiredInt($input, 'user_id'), $actor)),
            'users_store' => $this->userPayload($this->createUser->execute($acting, $this->text($input, 'name'))),
            'users_update' => $this->userPayload($this->updateUser->execute($acting, $this->visibleUser($this->requiredInt($input, 'user_id'), $actor)->id, $this->text($input, 'name'))),
            'users_destroy' => $this->removeUser($acting, $actor, $this->requiredInt($input, 'user_id')),
            'contacts_index' => [
                'contacts' => array_map(fn (Contact $contact) => $this->contactListPayload($contact), $this->crm->searchContacts($actor->id, $this->query($input), self::LIMIT, 0)),
                'count' => $this->crm->countContacts($actor->id),
            ],
            'contacts_show' => $this->contactPayload($this->visibleContact($this->requiredInt($input, 'contact_id'), $actor)),
            'contacts_store' => $this->contactPayload($this->createContact->execute($acting, $this->text($input, 'name'))),
            'contacts_update' => $this->contactPayload($this->updateContact->execute(
                $acting,
                $this->visibleContact($this->requiredInt($input, 'contact_id'), $actor)->id,
                $this->text($input, 'name'),
                $this->text($input, 'email'),
                $this->text($input, 'company'),
            )),
            'contacts_destroy' => $this->deleted($this->deleteContact, $acting, $this->visibleContact($this->requiredInt($input, 'contact_id'), $actor)->id),
            'contacts_qualify' => $this->contactPayload($this->qualifyContact->execute(
                $acting,
                $this->visibleContact($this->requiredInt($input, 'contact_id'), $actor)->id,
                $this->text($input, 'email'),
                $this->text($input, 'company'),
            )),
            'contacts_estimates_store' => $this->contactPayload($this->sendEstimate->execute(
                $acting,
                $this->visibleContact($this->requiredInt($input, 'contact_id'), $actor)->id,
                $this->lines($input),
            )),
            'products_index' => [
                'products' => array_map(fn (Product $product) => $this->productPayload($product), $this->crm->searchProducts($this->query($input), self::LIMIT, 0)),
                'count' => $this->crm->countProducts(),
            ],
            'products_show' => $this->productPayload($this->visibleProduct($this->requiredInt($input, 'product_id'), $actor)),
            'products_store' => $this->productPayload($this->createProduct->execute($acting, $this->text($input, 'name'), $this->requiredInt($input, 'unit_price'))),
            'products_update' => $this->productPayload($this->updateProduct->execute(
                $acting,
                $this->visibleProduct($this->requiredInt($input, 'product_id'), $actor)->id,
                $this->text($input, 'name'),
                $this->requiredInt($input, 'unit_price'),
            )),
            'products_destroy' => $this->deleted($this->deleteProduct, $acting, $this->visibleProduct($this->requiredInt($input, 'product_id'), $actor)->id),
            'estimates_index' => [
                'estimates' => array_map(fn (ListedEstimate $estimate) => [
                    'id' => $estimate->id,
                    'contact_name' => $estimate->contactName,
                    'grand_total' => $estimate->grandTotal,
                ], $this->crm->searchEstimates($actor->id, $this->query($input), self::LIMIT, 0)),
                'count' => $this->crm->countEstimates($actor->id),
            ],
            'estimates_show' => $this->estimatePayload($this->visibleEstimate($this->requiredInt($input, 'estimate_id'), $actor)),
            'estimates_update' => $this->replaceEstimate($acting, $this->visibleEstimate($this->requiredInt($input, 'estimate_id'), $actor)->id, $input),
            'estimates_destroy' => $this->deleted($this->deleteEstimate, $acting, $this->visibleEstimate($this->requiredInt($input, 'estimate_id'), $actor)->id),
            default => throw new \RuntimeException('Tool is missing.'),
        };
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function chooseUser(VoiceRun $run, array $input): array
    {
        $user = $this->crm->findUser($this->requiredInt($input, 'user_id'));
        if ($user === null) {
            throw new \RuntimeException('User is missing.');
        }

        $this->runs->setActingUser($run->id, $user->id);

        return $this->userPayload($user);
    }

    /**
     * @return list<array{name: string, contacts: list<array{id: int|null, name: string, column: string}>}>
     */
    private function board(User $actor): array
    {
        $contacts = $this->crm->listContacts($actor->id);
        $columns = [];
        foreach ([Column::NEW, Column::QUALIFIED, Column::ESTIMATE_SENT] as $name) {
            $columns[] = [
                'name' => $name,
                'contacts' => array_values(array_map(
                    fn (Contact $contact) => $this->contactListPayload($contact),
                    array_filter($contacts, fn (Contact $contact) => $contact->column === $name),
                )),
            ];
        }

        return $columns;
    }

    /**
     * @return array{deleted: true}
     */
    private function removeUser(Actor $acting, User $actor, int $userId): array
    {
        $this->visibleUser($userId, $actor);
        $this->deleteUser->execute($acting, $userId);

        return ['deleted' => true];
    }

    /**
     * @return array{deleted: true}
     */
    private function deleted(DeleteContact|DeleteProduct|DeleteEstimate $action, Actor $acting, int $id): array
    {
        $action->execute($acting, $id);

        return ['deleted' => true];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{updated: true}
     */
    private function replaceEstimate(Actor $acting, int $estimateId, array $input): array
    {
        $this->updateEstimate->execute($acting, $estimateId, $this->lines($input));

        return ['updated' => true];
    }

    private function visibleUser(int $id, User $actor): User
    {
        $user = $this->crm->findUser($id);
        if ($user === null || ! $this->policy->viewUser($actor, $user)) {
            throw new \RuntimeException('User is missing.');
        }

        return $user;
    }

    private function visibleContact(int $id, User $actor): Contact
    {
        $contact = $this->crm->findContact($id);
        if ($contact === null || ! $this->policy->viewContact($actor, $contact)) {
            throw new \RuntimeException('Contact is missing.');
        }

        return $contact;
    }

    private function visibleProduct(int $id, User $actor): Product
    {
        $product = $this->crm->findProduct($id);
        if ($product === null || ! $this->policy->viewProduct($actor, $product)) {
            throw new \RuntimeException('Product is missing.');
        }

        return $product;
    }

    private function visibleEstimate(int $id, User $actor): EstimateDetail
    {
        $estimate = $this->crm->findEstimate($id);
        if ($estimate === null || ! $this->policy->viewEstimate($actor, $estimate->userId)) {
            throw new \RuntimeException('Estimate is missing.');
        }

        return $estimate;
    }

    /**
     * @return array{id: int, name: string}
     */
    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
        ];
    }

    /**
     * @return array{id: int|null, name: string, column: string}
     */
    private function contactListPayload(Contact $contact): array
    {
        return [
            'id' => $contact->id,
            'name' => $contact->name,
            'column' => $contact->column,
        ];
    }

    /**
     * @return array{id: int|null, name: string, email: string|null, company: string|null, column: string, sent_email_body: string|null, grand_total: int|null}
     */
    private function contactPayload(Contact $contact): array
    {
        return [
            'id' => $contact->id,
            'name' => $contact->name,
            'email' => $contact->email,
            'company' => $contact->company,
            'column' => $contact->column,
            'sent_email_body' => $contact->sentEmailBody,
            'grand_total' => $contact->grandTotal,
        ];
    }

    /**
     * @return array{id: int, name: string, unit_price: int}
     */
    private function productPayload(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'unit_price' => $product->unitPrice,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function estimatePayload(EstimateDetail $estimate): array
    {
        return [
            'id' => $estimate->id,
            'contact_id' => $estimate->contactId,
            'contact_name' => $estimate->contactName,
            'grand_total' => $estimate->grandTotal,
            'lines' => array_map(fn ($line) => [
                'product_id' => $line->productId,
                'product_name' => $line->productName,
                'quantity' => $line->quantity,
                'unit_price' => $line->unitPrice,
            ], $estimate->lines),
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function query(array $input): string
    {
        $query = $input['query'] ?? '';

        return is_string($query) ? trim($query) : '';
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function text(array $input, string $key): string
    {
        $value = $input[$key] ?? '';

        return is_string($value) ? $value : '';
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function requiredInt(array $input, string $key): int
    {
        if (! array_key_exists($key, $input) || is_array($input[$key]) || ! is_numeric($input[$key])) {
            throw new CrmException('voice.fields.required', 'F38', [
                $key => 'voice.fields.required',
            ]);
        }

        return (int) $input[$key];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return list<array{product_id: int, quantity: int}>
     */
    private function lines(array $input): array
    {
        $lines = $input['lines'] ?? null;
        if (! is_array($lines)) {
            throw new CrmException('voice.fields.required', 'F38', [
                'lines' => 'voice.fields.required',
            ]);
        }

        $normalized = [];
        foreach ($lines as $line) {
            if (! is_array($line)) {
                continue;
            }

            $normalized[] = [
                'product_id' => (int) ($line['product_id'] ?? 0),
                'quantity' => (int) ($line['quantity'] ?? 0),
            ];
        }

        return $normalized;
    }

    /**
     * @param  array<string, string>|null  $fields
     */
    private function error(string $code, ?string $featureId, int $status, ?array $fields = null): JsonResponse
    {
        $error = [
            'code' => $code,
            'feature_id' => $featureId,
        ];
        if ($fields !== null && $fields !== []) {
            $error['fields'] = $fields;
        }

        return response()->json($error, $status);
    }
}
