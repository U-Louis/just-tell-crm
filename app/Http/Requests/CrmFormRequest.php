<?php

namespace App\Http\Requests;

use App\Domain\Crm\CrmRepository;
use App\Domain\Crm\User;
use App\Http\CrmLog;
use App\Policies\CrmPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class CrmFormRequest extends FormRequest
{
    private ?User $actingUser = null;

    private bool $actingUserResolved = false;

    abstract protected function featureId(): ?string;

    public function authorize(): bool
    {
        return app(CrmPolicy::class)->participate($this->resolveActingUser());
    }

    public function actor(): User
    {
        $actor = $this->resolveActingUser();
        if ($actor === null) {
            throw new \LogicException('Acting user is missing.');
        }

        return $actor;
    }

    protected function failedAuthorization(): void
    {
        $this->refuse('user.required', $this->featureId());
    }

    /**
     * @param  array<string, string>  $fields
     */
    protected function refuse(string $code, ?string $featureId, array $fields = []): never
    {
        CrmLog::write($this, $featureId, 'refused', $code);

        $error = [
            'code' => $code,
            'feature_id' => $featureId,
        ];
        if ($fields !== []) {
            $error['fields'] = $fields;
        }

        throw new HttpResponseException(back()->with('error', $error));
    }

    private function resolveActingUser(): ?User
    {
        if ($this->actingUserResolved) {
            return $this->actingUser;
        }

        $this->actingUserResolved = true;
        $id = $this->session()->get('acting_user_id');
        if (! is_int($id) && ! (is_string($id) && ctype_digit($id))) {
            return null;
        }

        $this->actingUser = app(CrmRepository::class)->findUser((int) $id);

        return $this->actingUser;
    }
}
