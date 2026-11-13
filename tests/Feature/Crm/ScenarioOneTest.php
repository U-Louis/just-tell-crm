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
                ->missing('contact.notes'));
    }

    public function test_f5_send_without_email_is_refused_and_leaves_qualified(): void
    {
        $ada = User::factory()->create(['name' => 'Ada']);
        $cable = ProductRecord::query()->create(['name' => 'Cable', 'unit_price' => 10]);
        $lamp = ProductRecord::query()->create(['name' => 'Lamp', 'unit_price' => 5]);
        $contact = ContactRecord::query()->create([
            'name' => 'Northwind',
            'email' => null,
            'company' => 'Northwind',
            'column' => 'qualified',
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

    public function test_state_change_without_an_acting_user_is_refused(): void
    {
        $response = $this->from(route('home'))
            ->post(route('contacts.store'), ['name' => 'Northwind']);

        $response->assertStatus(302);
        $response->assertSessionHas('error.code', 'user.required');
        $response->assertSessionHas('error.feature_id', 'F1');
        $this->assertDatabaseCount('contacts', 0);
    }
}
