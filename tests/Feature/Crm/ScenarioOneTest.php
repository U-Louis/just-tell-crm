<?php

namespace Tests\Feature\Crm;

use App\Infrastructure\Persistence\Crm\ContactRecord;
use App\Infrastructure\Persistence\Crm\ProductRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ScenarioOneTest extends TestCase
{
    use RefreshDatabase;

    public function test_f1_create_contact_redirects_and_stores_column_new(): void
    {
        $ada = User::factory()->create(['name' => 'Ada']);

        $response = $this->withSession(['acting_user_id' => $ada->id])
            ->from(route('home'))
            ->post(route('contacts.store'), ['name' => 'Northwind']);

        $response->assertStatus(302);
        $response->assertRedirect(route('home'));
        $this->assertDatabaseHas('contacts', [
            'name' => 'Northwind',
            'column' => 'new',
            'user_id' => $ada->id,
        ]);

        $this->withSession(['acting_user_id' => $ada->id])
            ->get(route('home'))
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Kanban')
                ->where('columns.0.name', 'new')
                ->where('columns.0.contacts.0.name', 'Northwind')
                ->missing('columns.0.contacts.0.email'));
    }

    public function test_f2_empty_name_is_refused_and_writes_nothing(): void
    {
        $ada = User::factory()->create(['name' => 'Ada']);

        $response = $this->withSession(['acting_user_id' => $ada->id])
            ->from(route('home'))
            ->post(route('contacts.store'), ['name' => '']);

        $response->assertStatus(302);
        $response->assertSessionHas('error.code', 'contact.name.required');
        $response->assertSessionHas('error.feature_id', 'F2');
        $this->assertDatabaseCount('contacts', 0);
    }

    public function test_f3_qualify_moves_the_contact_to_qualified(): void
    {
        $ada = User::factory()->create(['name' => 'Ada']);
        $this->withSession(['acting_user_id' => $ada->id])
            ->from(route('home'))
            ->post(route('contacts.store'), ['name' => 'Northwind']);
        $contactId = (int) ContactRecord::query()->where('name', 'Northwind')->value('id');

        $response = $this->withSession(['acting_user_id' => $ada->id])
            ->from(route('contacts.show', $contactId))
            ->post(route('contacts.qualify', $contactId), [
                'email' => 'northwind@example.com',
                'company' => 'Northwind',
            ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('contacts.show', $contactId));
        $this->assertDatabaseHas('contacts', [
            'name' => 'Northwind',
            'email' => 'northwind@example.com',
            'company' => 'Northwind',
            'column' => 'qualified',
            'validated' => true,
        ]);
    }

    public function test_f4_send_moves_to_estimate_sent_and_records_the_test_body(): void
    {
        $ada = User::factory()->create(['name' => 'Ada']);
        $cable = ProductRecord::query()->create(['name' => 'Cable', 'unit_price' => 10]);
        $lamp = ProductRecord::query()->create(['name' => 'Lamp', 'unit_price' => 5]);
        $this->withSession(['acting_user_id' => $ada->id])
            ->post(route('contacts.store'), ['name' => 'Northwind']);
        $contactId = (int) ContactRecord::query()->where('name', 'Northwind')->value('id');
        $this->withSession(['acting_user_id' => $ada->id])
            ->post(route('contacts.qualify', $contactId), [
                'email' => 'northwind@example.com',
                'company' => 'Northwind',
            ]);

        $response = $this->withSession(['acting_user_id' => $ada->id])
            ->from(route('contacts.show', $contactId))
            ->post(route('contacts.estimates.store', $contactId), [
                'lines' => [
                    ['product_id' => $cable->id, 'quantity' => 2],
                    ['product_id' => $lamp->id, 'quantity' => 1],
                ],
            ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('contacts.show', $contactId));
        $this->assertDatabaseHas('contacts', [
            'id' => $contactId,
            'column' => 'estimate sent',
        ]);
        $this->assertDatabaseHas('estimates', [
            'contact_id' => $contactId,
            'grand_total' => 25,
        ]);
        $this->assertDatabaseHas('sent_emails', [
            'contact_id' => $contactId,
            'to_address' => 'northwind@example.com',
            'body' => 'This is a test.',
        ]);

        $this->withSession(['acting_user_id' => $ada->id])
            ->get(route('contacts.show', $contactId))
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Contacts/Show')
                ->where('contact.name', 'Northwind')
                ->where('contact.email', 'northwind@example.com')
                ->where('contact.company', 'Northwind')
                ->where('contact.column', 'estimate sent')
                ->where('contact.sentEmailBody', 'This is a test.')
                ->where('contact.grandTotal', 25)
                ->where('contact.notes', null));
    }

    public function test_f5_send_without_email_is_refused_and_leaves_qualified(): void
    {
        $ada = User::factory()->create(['name' => 'Ada']);
        $cable = ProductRecord::query()->create(['name' => 'Cable', 'unit_price' => 10]);
        $lamp = ProductRecord::query()->create(['name' => 'Lamp', 'unit_price' => 5]);
        $contact = ContactRecord::query()->create([
            'user_id' => $ada->id,
            'name' => 'Northwind',
            'email' => null,
            'company' => 'Northwind',
            'column' => 'qualified',
            'validated' => true,
        ]);

        $response = $this->withSession(['acting_user_id' => $ada->id])
            ->from(route('contacts.show', $contact))
            ->post(route('contacts.estimates.store', $contact), [
                'lines' => [
                    ['product_id' => $cable->id, 'quantity' => 2],
                    ['product_id' => $lamp->id, 'quantity' => 1],
                ],
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error.code', 'contact.email.required');
        $response->assertSessionHas('error.feature_id', 'F5');
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'column' => 'qualified',
            'email' => null,
        ]);
        $this->assertDatabaseCount('estimates', 0);
        $this->assertDatabaseCount('sent_emails', 0);
    }

    public function test_send_before_the_contact_is_validated_is_refused(): void
    {
        $ada = User::factory()->create(['name' => 'Ada']);
        $cable = ProductRecord::query()->create(['name' => 'Cable', 'unit_price' => 10]);
        $this->withSession(['acting_user_id' => $ada->id])
            ->post(route('contacts.store'), ['name' => 'Northwind']);
        $contactId = (int) ContactRecord::query()->where('name', 'Northwind')->value('id');

        $response = $this->withSession(['acting_user_id' => $ada->id])
            ->from(route('contacts.show', $contactId))
            ->post(route('contacts.estimates.store', $contactId), [
                'lines' => [
                    ['product_id' => $cable->id, 'quantity' => 2],
                ],
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error.code', 'contact.not_validated');
        $response->assertSessionHas('error.feature_id', 'F4');
        $this->assertDatabaseHas('contacts', [
            'id' => $contactId,
            'column' => 'new',
            'validated' => false,
        ]);
        $this->assertDatabaseCount('estimates', 0);
        $this->assertDatabaseCount('sent_emails', 0);
    }

    public function test_missing_acting_user_defaults_to_the_first_user_by_name(): void
    {
        $ben = User::factory()->create(['name' => 'Ben']);
        $ada = User::factory()->create(['name' => 'Ada']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSessionHas('acting_user_id', $ada->id)
            ->assertInertia(fn (Assert $page) => $page
                ->where('actingUser.id', $ada->id)
                ->where('actingUser.name', 'Ada')
                ->where('users.0.name', 'Ada'));

        $this->withSession(['acting_user_id' => $ben->id])
            ->get(route('home'))
            ->assertSessionHas('acting_user_id', $ben->id);

        $this->withSession(['acting_user_id' => 9999])
            ->get(route('home'))
            ->assertSessionHas('acting_user_id', $ada->id);
    }

    public function test_state_change_without_an_acting_user_is_refused(): void
    {
        $response = $this->from(route('home'))
            ->post(route('contacts.store'), ['name' => 'Northwind']);

        $response->assertStatus(302);
        $response->assertSessionHas('error.code', 'user.required');
        $response->assertSessionHas('error.feature_id', 'F1');
        $this->assertDatabaseCount('contacts', 0);
    }

    public function test_kanban_contact_list_and_estimate_list_show_only_the_acting_users_records(): void
    {
        $ada = User::factory()->create(['name' => 'Ada']);
        $ben = User::factory()->create(['name' => 'Ben']);
        $cable = ProductRecord::query()->create(['name' => 'Cable', 'unit_price' => 10]);
        $lamp = ProductRecord::query()->create(['name' => 'Lamp', 'unit_price' => 5]);
        $this->withSession(['acting_user_id' => $ada->id])
            ->post(route('contacts.store'), ['name' => 'Northwind']);
        $contactId = (int) ContactRecord::query()->where('name', 'Northwind')->value('id');
        $this->withSession(['acting_user_id' => $ada->id])
            ->post(route('contacts.qualify', $contactId), [
                'email' => 'northwind@example.com',
                'company' => 'Northwind',
            ]);
        $this->withSession(['acting_user_id' => $ada->id])
            ->post(route('contacts.estimates.store', $contactId), [
                'lines' => [
                    ['product_id' => $cable->id, 'quantity' => 2],
                    ['product_id' => $lamp->id, 'quantity' => 1],
                ],
            ]);

        $this->withSession(['acting_user_id' => $ada->id])
            ->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Kanban')
                ->where('columns.2.contacts.0.name', 'Northwind'));

        $this->withSession(['acting_user_id' => $ben->id])
            ->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Kanban')
                ->where('columns.0.contacts', [])
                ->where('columns.1.contacts', [])
                ->where('columns.2.contacts', []));

        $this->withSession(['acting_user_id' => $ada->id])
            ->get(route('contacts.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Contacts/Index')
                ->where('contacts.0.name', 'Northwind')
                ->where('contacts.0.column', 'estimate sent')
                ->missing('contacts.0.email'));

        $this->withSession(['acting_user_id' => $ben->id])
            ->get(route('contacts.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Contacts/Index')
                ->where('contacts', []));

        $this->withSession(['acting_user_id' => $ada->id])
            ->get(route('estimates.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Estimates/Index')
                ->where('estimates.0.contactName', 'Northwind')
                ->where('estimates.0.grandTotal', 25)
                ->missing('estimates.0.email'));

        $this->withSession(['acting_user_id' => $ben->id])
            ->get(route('estimates.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Estimates/Index')
                ->where('estimates', []));

        $this->withSession(['acting_user_id' => $ada->id])
            ->get(route('contacts.show', $contactId))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('products.0.name', 'Cable')
                ->where('products.1.name', 'Lamp'));

        $this->withSession(['acting_user_id' => $ben->id])
            ->get(route('contacts.show', $contactId))
            ->assertNotFound();

        $this->withSession(['acting_user_id' => $ben->id])
            ->post(route('contacts.qualify', $contactId), [
                'email' => 'other@example.com',
                'company' => 'Other',
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('contacts', [
            'id' => $contactId,
            'user_id' => $ada->id,
            'email' => 'northwind@example.com',
            'column' => 'estimate sent',
        ]);
    }

    public function test_kanban_move_url_points_at_the_next_columns_edit_anchor(): void
    {
        $ada = User::factory()->create(['name' => 'Ada']);
        $cable = ProductRecord::query()->create(['name' => 'Cable', 'unit_price' => 10]);
        $lamp = ProductRecord::query()->create(['name' => 'Lamp', 'unit_price' => 5]);
        $this->withSession(['acting_user_id' => $ada->id])
            ->post(route('contacts.store'), ['name' => 'Northwind']);
        $contactId = (int) ContactRecord::query()->where('name', 'Northwind')->value('id');

        $this->withSession(['acting_user_id' => $ada->id])
            ->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Kanban')
                ->where('columns.0.contacts.0.nextColumn', 'qualified')
                ->where('columns.0.contacts.0.moveUrl', route('contacts.show', $contactId).'#qualify'));

        $this->withSession(['acting_user_id' => $ada->id])
            ->post(route('contacts.qualify', $contactId), [
                'email' => 'northwind@example.com',
                'company' => 'Northwind',
            ]);

        $this->withSession(['acting_user_id' => $ada->id])
            ->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('columns.1.contacts.0.nextColumn', 'estimate sent')
                ->where('columns.1.contacts.0.moveUrl', route('contacts.show', $contactId).'#estimate'));

        $this->withSession(['acting_user_id' => $ada->id])
            ->post(route('contacts.estimates.store', $contactId), [
                'lines' => [
                    ['product_id' => $cable->id, 'quantity' => 2],
                    ['product_id' => $lamp->id, 'quantity' => 1],
                ],
            ]);

        $this->withSession(['acting_user_id' => $ada->id])
            ->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('columns.2.contacts.0.nextColumn', null)
                ->where('columns.2.contacts.0.moveUrl', null));
    }
}
