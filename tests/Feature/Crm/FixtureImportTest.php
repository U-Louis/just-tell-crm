<?php

namespace Tests\Feature\Crm;

use App\Domain\Crm\CompanySize;
use App\Domain\Crm\ProductCategory;
use App\Infrastructure\Persistence\Crm\ContactRecord;
use App\Infrastructure\Persistence\Crm\EstimateLineRecord;
use App\Infrastructure\Persistence\Crm\EstimateRecord;
use App\Infrastructure\Persistence\Crm\ProductRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FixtureImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_saves_the_new_columns_and_keeps_the_column(): void
    {
        $ada = User::factory()->create(['name' => 'Ada']);
        $ben = User::factory()->create(['name' => 'Ben']);

        $this->withSession(['acting_user_id' => $ada->id])
            ->post(route('contacts.store'), [
                'name' => 'Northwind',
                'description' => 'A harbor trader',
                'notes' => 'Call next week',
                'company_size' => '11-50',
                'owner_id' => $ben->id,
            ])
            ->assertRedirect(route('home'));

        $contact = ContactRecord::query()->where('name', 'Northwind')->first();
        $this->assertNotNull($contact);
        $this->assertSame('new', $contact->column);
        $this->assertSame('A harbor trader', $contact->description);
        $this->assertSame('Call next week', $contact->notes);
        $this->assertSame('11-50', $contact->company_size);
        $this->assertSame($ben->id, (int) $contact->owner_id);
        $this->assertSame($ada->id, (int) $contact->user_id);

        $this->withSession(['acting_user_id' => $ada->id])
            ->put(route('contacts.update', $contact), [
                'name' => 'Northwind Updated',
                'email' => 'northwind@example.com',
                'company' => 'Northwind',
                'description' => 'Updated note',
                'notes' => '',
                'company_size' => '1-10',
                'owner_id' => $ada->id,
            ])
            ->assertRedirect(route('contacts.show', $contact));

        $contact->refresh();
        $this->assertSame('new', $contact->column);
        $this->assertSame('Updated note', $contact->description);
        $this->assertNull($contact->notes);
        $this->assertSame('1-10', $contact->company_size);
        $this->assertSame($ada->id, (int) $contact->owner_id);
    }

    public function test_invalid_company_size_is_refused(): void
    {
        $ada = User::factory()->create(['name' => 'Ada']);

        $this->from(route('contacts.create'))
            ->withSession(['acting_user_id' => $ada->id])
            ->post(route('contacts.store'), [
                'name' => 'Northwind',
                'company_size' => 'huge',
            ])
            ->assertRedirect(route('contacts.create'))
            ->assertSessionHas('error.code', 'contact.company_size.invalid');

        $this->assertDatabaseMissing('contacts', ['name' => 'Northwind']);
    }

    public function test_import_buttons_create_fixture_rows(): void
    {
        $ada = User::factory()->create(['name' => 'Ada']);

        $this->from(route('contacts.index'))
            ->withSession(['acting_user_id' => $ada->id])
            ->post(route('contacts.import'))
            ->assertRedirect(route('contacts.index'));

        $contact = ContactRecord::query()->where('user_id', $ada->id)->first();
        $this->assertNotNull($contact);
        $this->assertSame('new', $contact->column);
        $this->assertNotSame('', (string) $contact->email);
        $this->assertNotSame('', (string) $contact->company);
        $this->assertNotSame('', (string) $contact->description);
        $this->assertNotSame('', (string) $contact->notes);
        $this->assertContains($contact->company_size, CompanySize::VALUES);

        $this->from(route('products.index'))
            ->withSession(['acting_user_id' => $ada->id])
            ->post(route('products.import'))
            ->assertRedirect(route('products.index'));

        $product = ProductRecord::query()->first();
        $this->assertNotNull($product);
        $this->assertNotNull($product->description);
        $this->assertContains($product->category, ProductCategory::VALUES);

        $this->from(route('estimates.index'))
            ->withSession(['acting_user_id' => $ada->id])
            ->post(route('estimates.import'))
            ->assertRedirect(route('estimates.index'));

        $estimate = EstimateRecord::query()->first();
        $this->assertNotNull($estimate);
        $this->assertNotNull($estimate->sent_at);
        $lines = EstimateLineRecord::query()->where('estimate_id', $estimate->id)->count();
        $this->assertGreaterThanOrEqual(1, $lines);
        $this->assertLessThanOrEqual(3, $lines);
        $this->assertSame(1, ContactRecord::query()->count());
    }

    public function test_create_pages_offer_the_fixed_lists(): void
    {
        $ada = User::factory()->create(['name' => 'Ada']);

        $this->withSession(['acting_user_id' => $ada->id])
            ->get(route('contacts.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('companySizes', CompanySize::VALUES)
                ->where('urls.store', route('contacts.store')));

        $this->withSession(['acting_user_id' => $ada->id])
            ->get(route('products.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('categories', ProductCategory::VALUES));
    }

    public function test_sending_an_estimate_stamps_sent_at(): void
    {
        $ada = User::factory()->create(['name' => 'Ada']);
        $contact = ContactRecord::query()->create([
            'user_id' => $ada->id,
            'owner_id' => $ada->id,
            'name' => 'Northwind',
            'email' => 'northwind@example.com',
            'company' => 'Northwind',
            'column' => 'qualified',
            'validated' => true,
        ]);
        $cable = ProductRecord::query()->create(['name' => 'Cable', 'unit_price' => 10]);

        $this->withSession(['acting_user_id' => $ada->id])
            ->post(route('contacts.estimates.store', $contact), [
                'lines' => [
                    ['product_id' => $cable->id, 'quantity' => 2],
                ],
            ])
            ->assertRedirect();

        $estimate = EstimateRecord::query()->where('contact_id', $contact->id)->first();
        $this->assertNotNull($estimate);
        $this->assertNotNull($estimate->sent_at);
    }
}
