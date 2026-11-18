<?php

namespace Tests\Feature\Crm;

use App\Infrastructure\Persistence\Crm\ContactRecord;
use App\Infrastructure\Persistence\Crm\EstimateRecord;
use App\Infrastructure\Persistence\Crm\ProductRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DraftUseCasesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_crud_refusals_page_size_and_count(): void
    {
        $ada = User::factory()->create(['name' => 'Ada']);
        ContactRecord::query()->create([
            'user_id' => $ada->id,
            'name' => 'Northwind',
            'column' => 'new',
        ]);

        $this->from(route('users.index'))
            ->actingAsUser($ada)
            ->post(route('users.store'), ['name' => ''])
            ->assertRedirect()
            ->assertSessionHas('error.code', 'user.name.required')
            ->assertSessionHas('error.feature_id', 'F8');

        $this->from(route('users.index'))
            ->actingAsUser($ada)
            ->post(route('users.store'), ['name' => 'Ada'])
            ->assertRedirect()
            ->assertSessionHas('error.code', 'user.name.taken')
            ->assertSessionHas('error.feature_id', 'F9');

        $this->from(route('users.show', $ada))
            ->actingAsUser($ada)
            ->delete(route('users.destroy', $ada))
            ->assertRedirect()
            ->assertSessionHas('error.code', 'user.delete.blocked')
            ->assertSessionHas('error.feature_id', 'F11');
        $this->assertDatabaseHas('users', ['id' => $ada->id]);

        $dana = User::factory()->create(['name' => 'Dana']);
        $this->withSession(['acting_user_id' => $dana->id])
            ->delete(route('users.destroy', $dana))
            ->assertRedirect(route('users.index'))
            ->assertSessionMissing('acting_user_id');
        $this->assertDatabaseMissing('users', ['id' => $dana->id]);

        $this->actingAsUser($ada)
            ->put(route('users.update', $ada), ['name' => 'Ada Lovelace'])
            ->assertRedirect(route('users.show', $ada));
        $this->assertDatabaseHas('users', ['id' => $ada->id, 'name' => 'Ada Lovelace']);

        User::query()->whereKey($ada->id)->update(['name' => 'Ada']);
        foreach (range(1, 15) as $index) {
            User::factory()->create(['name' => sprintf('User %02d', $index)]);
        }

        $this->actingAsUser($ada)
            ->get(route('users.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Users/Index')
                ->where('count', 16)
                ->has('users', 15)
                ->where('users.0.name', 'Ada'));

        $this->actingAsUser($ada)
            ->get(route('users.index', ['page' => 2]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('count', 16)
                ->has('users', 1));

        $this->actingAsUser($ada)
            ->get(route('users.index', ['q' => 'User 15']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('count', 16)
                ->has('users', 1)
                ->where('users.0.name', 'User 15'));

        $this->flushSession()
            ->get(route('users.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('users', [])
                ->where('count', 0));

        $this->flushSession()->get(route('users.show', $ada))->assertNotFound();

        $this->flushSession()
            ->from(route('home'))
            ->post(route('users.store'), ['name' => 'Cam'])
            ->assertSessionHas('error.code', 'user.required')
            ->assertSessionHas('error.feature_id', 'F7');
    }

    public function test_contact_update_keeps_the_column_and_delete_removes_owned_rows(): void
    {
        [$ada, $ben, $contactId] = $this->sentNorthwind();

        $this->from(route('contacts.show', $contactId))
            ->actingAsUser($ada)
            ->put(route('contacts.update', $contactId), [
                'name' => '',
                'email' => 'northwind@example.com',
                'company' => 'Northwind',
            ])
            ->assertSessionHas('error.code', 'contact.name.required')
            ->assertSessionHas('error.feature_id', 'F14');

        $this->actingAsUser($ada)
            ->put(route('contacts.update', $contactId), [
                'name' => 'Northwind Traders',
                'email' => '',
                'company' => '',
            ])
            ->assertRedirect(route('contacts.show', $contactId));

        $this->assertDatabaseHas('contacts', [
            'id' => $contactId,
            'name' => 'Northwind Traders',
            'email' => null,
            'company' => null,
            'column' => 'estimate sent',
        ]);
        $this->assertDatabaseCount('sent_emails', 1);

        $this->actingAsUser($ben)
            ->put(route('contacts.update', $contactId), [
                'name' => 'Stolen',
                'email' => 'other@example.com',
                'company' => 'Other',
            ])
            ->assertNotFound();
        $this->actingAsUser($ben)->get(route('contacts.show', $contactId))->assertNotFound();
        $this->actingAsUser($ben)->delete(route('contacts.destroy', $contactId))->assertNotFound();
        $this->assertDatabaseHas('contacts', ['id' => $contactId, 'name' => 'Northwind Traders']);

        $this->actingAsUser($ada)
            ->get(route('contacts.index', ['q' => 'northwind@example.com']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('count', 1)
                ->where('contacts', []));

        $this->actingAsUser($ada)
            ->delete(route('contacts.destroy', $contactId))
            ->assertRedirect(route('contacts.index'));

        $this->assertDatabaseCount('contacts', 0);
        $this->assertDatabaseCount('estimates', 0);
        $this->assertDatabaseCount('estimate_lines', 0);
        $this->assertDatabaseCount('sent_emails', 0);
    }

    public function test_product_price_change_and_blocked_delete(): void
    {
        [$ada, , $contactId, $estimateId, $cable] = $this->sentNorthwind();
        $spare = ProductRecord::query()->create(['name' => 'Spare', 'unit_price' => 1]);

        $this->from(route('products.index'))
            ->actingAsUser($ada)
            ->post(route('products.store'), ['name' => '', 'unit_price' => 4])
            ->assertSessionHas('error.code', 'product.name.required')
            ->assertSessionHas('error.feature_id', 'F17');

        $this->from(route('products.index'))
            ->actingAsUser($ada)
            ->post(route('products.store'), ['name' => 'Cable', 'unit_price' => 4])
            ->assertSessionHas('error.code', 'product.name.taken')
            ->assertSessionHas('error.feature_id', 'F18');

        $this->from(route('products.index'))
            ->actingAsUser($ada)
            ->post(route('products.store'), ['name' => 'Plug', 'unit_price' => -1])
            ->assertSessionHas('error.code', 'product.unit_price.invalid')
            ->assertSessionHas('error.feature_id', 'F19');

        $this->actingAsUser($ada)
            ->put(route('products.update', $cable), ['name' => 'Cable', 'unit_price' => 12])
            ->assertRedirect(route('products.show', $cable));

        $this->assertDatabaseHas('estimate_lines', [
            'estimate_id' => $estimateId,
            'product_id' => $cable->id,
            'unit_price' => 10,
        ]);
        $this->assertDatabaseHas('estimates', [
            'id' => $estimateId,
            'grand_total' => 25,
        ]);
        $this->assertDatabaseHas('contacts', [
            'id' => $contactId,
            'column' => 'estimate sent',
        ]);

        $this->from(route('products.show', $cable))
            ->actingAsUser($ada)
            ->delete(route('products.destroy', $cable))
            ->assertSessionHas('error.code', 'product.delete.blocked')
            ->assertSessionHas('error.feature_id', 'F21');
        $this->assertDatabaseHas('products', ['id' => $cable->id]);

        $this->actingAsUser($ada)
            ->delete(route('products.destroy', $spare))
            ->assertRedirect(route('products.index'));
        $this->assertDatabaseMissing('products', ['id' => $spare->id]);

        $this->flushSession()->get(route('products.show', $spare))->assertNotFound();
        $this->flushSession()
            ->get(route('products.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('products', [])->where('count', 0));
    }

    public function test_estimate_update_recomputes_the_total_and_delete_leaves_the_column(): void
    {
        [$ada, $ben, $contactId, $estimateId, $cable, $lamp] = $this->sentNorthwind();
        $cable->update(['unit_price' => 12]);

        $this->from(route('estimates.show', $estimateId))
            ->actingAsUser($ada)
            ->put(route('estimates.update', $estimateId), [
                'lines' => [
                    ['product_id' => $cable->id, 'quantity' => 0],
                    ['product_id' => $lamp->id, 'quantity' => 0],
                ],
            ])
            ->assertSessionHas('error.code', 'estimate.lines.required')
            ->assertSessionHas('error.feature_id', 'F23');
        $this->assertDatabaseHas('estimates', ['id' => $estimateId, 'grand_total' => 25]);

        $this->actingAsUser($ada)
            ->put(route('estimates.update', $estimateId), [
                'lines' => [
                    ['product_id' => $cable->id, 'quantity' => 1],
                    ['product_id' => $lamp->id, 'quantity' => 0],
                ],
            ])
            ->assertRedirect(route('estimates.show', $estimateId));

        $this->assertDatabaseHas('estimates', ['id' => $estimateId, 'grand_total' => 12]);
        $this->assertDatabaseHas('estimate_lines', [
            'estimate_id' => $estimateId,
            'product_id' => $cable->id,
            'quantity' => 1,
            'unit_price' => 12,
        ]);
        $this->assertDatabaseMissing('estimate_lines', [
            'estimate_id' => $estimateId,
            'product_id' => $lamp->id,
        ]);
        $this->assertDatabaseHas('contacts', ['id' => $contactId, 'column' => 'estimate sent']);
        $this->assertDatabaseCount('sent_emails', 1);

        $this->actingAsUser($ben)->get(route('estimates.show', $estimateId))->assertNotFound();
        $this->actingAsUser($ben)->delete(route('estimates.destroy', $estimateId))->assertNotFound();

        $this->actingAsUser($ada)
            ->get(route('estimates.index', ['q' => 'Cable']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('count', 1)
                ->where('estimates', []));

        $this->actingAsUser($ada)
            ->get(route('estimates.index', ['q' => 'Northwind']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('count', 1)
                ->where('estimates.0.contactName', 'Northwind')
                ->where('estimates.0.grandTotal', 12));

        $this->actingAsUser($ada)
            ->delete(route('estimates.destroy', $estimateId))
            ->assertRedirect(route('estimates.index'));

        $this->assertDatabaseCount('estimates', 0);
        $this->assertDatabaseCount('estimate_lines', 0);
        $this->assertDatabaseHas('contacts', ['id' => $contactId, 'column' => 'estimate sent']);
        $this->assertDatabaseHas('sent_emails', [
            'contact_id' => $contactId,
            'body' => 'This is a test.',
        ]);
    }

    public function test_csv_headers_and_pdf_content_type(): void
    {
        [$ada, , $contactId, $estimateId] = $this->sentNorthwind();
        foreach (range(1, 15) as $index) {
            User::factory()->create(['name' => sprintf('User %02d', $index)]);
        }

        $users = $this->csvLines($this->actingAsUser($ada)->get(route('users.csv')));
        $this->assertSame('name', $users[0]);
        $this->assertCount(User::query()->count() + 1, $users);
        $this->assertGreaterThan(16, count($users));

        $filtered = $this->csvLines($this->actingAsUser($ada)->get(route('users.csv', ['q' => 'User 15'])));
        $this->assertCount(2, $filtered);
        $this->assertSame('name', $filtered[0]);
        $this->assertStringContainsString('User 15', $filtered[1]);

        $contacts = $this->csvLines($this->actingAsUser($ada)->get(route('contacts.csv')));
        $this->assertSame('name,column', $contacts[0]);
        $this->assertStringContainsString('Northwind', $contacts[1]);
        $this->assertStringNotContainsString('northwind@example.com', implode("\n", $contacts));

        $products = $this->csvLines($this->actingAsUser($ada)->get(route('products.csv')));
        $this->assertSame('name,unit_price', $products[0]);

        $estimates = $this->csvLines($this->actingAsUser($ada)->get(route('estimates.csv')));
        $this->assertSame('name,grand_total', $estimates[0]);
        $this->assertStringContainsString('Northwind', $estimates[1]);

        foreach ([
            route('users.pdf', $ada),
            route('contacts.pdf', $contactId),
            route('products.pdf', ProductRecord::query()->where('name', 'Cable')->value('id')),
            route('estimates.pdf', $estimateId),
        ] as $url) {
            $response = $this->actingAsUser($ada)->get($url);
            $response->assertOk();
            $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
            $this->assertStringStartsWith('%PDF', $response->getContent());
        }

        $this->actingAsUser($ada)->get(route('contacts.show', 9999))->assertNotFound();
        $this->flushSession()->get(route('contacts.pdf', $contactId))->assertNotFound();
    }

    /**
     * @return array{0: User, 1: User, 2: int, 3: int, 4: ProductRecord, 5: ProductRecord}
     */
    private function sentNorthwind(): array
    {
        $ada = User::factory()->create(['name' => 'Ada']);
        $ben = User::factory()->create(['name' => 'Ben']);
        $cable = ProductRecord::query()->create(['name' => 'Cable', 'unit_price' => 10]);
        $lamp = ProductRecord::query()->create(['name' => 'Lamp', 'unit_price' => 5]);
        $this->actingAsUser($ada)->post(route('contacts.store'), ['name' => 'Northwind']);
        $contactId = (int) ContactRecord::query()->where('name', 'Northwind')->value('id');
        $this->actingAsUser($ada)->post(route('contacts.qualify', $contactId), [
            'email' => 'northwind@example.com',
            'company' => 'Northwind',
        ]);
        $this->actingAsUser($ada)->post(route('contacts.estimates.store', $contactId), [
            'lines' => [
                ['product_id' => $cable->id, 'quantity' => 2],
                ['product_id' => $lamp->id, 'quantity' => 1],
            ],
        ]);
        $estimateId = (int) EstimateRecord::query()->where('contact_id', $contactId)->value('id');

        return [$ada, $ben, $contactId, $estimateId, $cable, $lamp];
    }

    private function actingAsUser(User $user): self
    {
        return $this->withSession(['acting_user_id' => $user->id]);
    }

    /**
     * @return list<string>
     */
    private function csvLines(TestResponse $response): array
    {
        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
        $lines = preg_split("/\r\n|\n|\r/", $response->streamedContent()) ?: [];

        return array_values(array_filter($lines, fn (string $line) => $line !== ''));
    }
}
