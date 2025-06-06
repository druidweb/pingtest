<?php

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
  $this->user = User::factory()->create([
    'account_id' => Account::create(['name' => 'Acme Corporation'])->id,
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'johndoe@example.com',
    'owner' => true,
  ]);

  $organization = $this->user->account->organizations()->create(['name' => 'Example Organization Inc.']);

  $this->user->account->contacts()->createMany([
    [
      'organization_id' => $organization->id,
      'first_name' => 'Martin',
      'last_name' => 'Abbott',
      'email' => 'martin.abbott@example.com',
      'phone' => '555-111-2222',
      'address' => '330 Glenda Shore',
      'city' => 'Murphyland',
      'region' => 'Tennessee',
      'country' => 'US',
      'postal_code' => '57851',
    ], [
      'organization_id' => $organization->id,
      'first_name' => 'Lynn',
      'last_name' => 'Kub',
      'email' => 'lynn.kub@example.com',
      'phone' => '555-333-4444',
      'address' => '199 Connelly Turnpike',
      'city' => 'Woodstock',
      'region' => 'Colorado',
      'country' => 'US',
      'postal_code' => '11623',
    ],
  ]);
});

test('can view contacts', function () {
  $this->actingAs($this->user)
    ->get('/contacts')
    ->assertInertia(fn (Assert $assert) => $assert
      ->component('Contacts/Index')
      ->has('contacts.data', 2)
      ->has('contacts.data.0', fn (Assert $assert) => $assert
        ->has('id')
        ->where('name', 'Martin Abbott')
        ->where('phone', '555-111-2222')
        ->where('city', 'Murphyland')
        ->where('deleted_at', null)
        ->has('organization', fn (Assert $assert) => $assert
          ->where('name', 'Example Organization Inc.')
        )
      )
      ->has('contacts.data.1', fn (Assert $assert) => $assert
        ->has('id')
        ->where('name', 'Lynn Kub')
        ->where('phone', '555-333-4444')
        ->where('city', 'Woodstock')
        ->where('deleted_at', null)
        ->has('organization', fn (Assert $assert) => $assert
          ->where('name', 'Example Organization Inc.')
        )
      )
    );
});

test('can search for contacts', function () {
  $this->actingAs($this->user)
    ->get('/contacts?search=Martin')
    ->assertInertia(fn (Assert $assert) => $assert
      ->component('Contacts/Index')
      ->where('filters.search', 'Martin')
      ->has('contacts.data', 1)
      ->has('contacts.data.0', fn (Assert $assert) => $assert
        ->has('id')
        ->where('name', 'Martin Abbott')
        ->where('phone', '555-111-2222')
        ->where('city', 'Murphyland')
        ->where('deleted_at', null)
        ->has('organization', fn (Assert $assert) => $assert
          ->where('name', 'Example Organization Inc.')
        )
      )
    );
});

test('cannot view deleted contacts', function () {
  $this->user->account->contacts()->firstWhere('first_name', 'Martin')->delete();

  $this->actingAs($this->user)
    ->get('/contacts')
    ->assertInertia(fn (Assert $assert) => $assert
      ->component('Contacts/Index')
      ->has('contacts.data', 1)
      ->where('contacts.data.0.name', 'Lynn Kub')
    );
});

test('can filter to view deleted contacts', function () {
  $this->user->account->contacts()->firstWhere('first_name', 'Martin')->delete();

  $this->actingAs($this->user)
    ->get('/contacts?trashed=with')
    ->assertInertia(fn (Assert $assert) => $assert
      ->component('Contacts/Index')
      ->has('contacts.data', 2)
      ->where('contacts.data.0.name', 'Martin Abbott')
      ->where('contacts.data.1.name', 'Lynn Kub')
    );
});

test('can view create contact page', function () {
  $this->actingAs($this->user)
    ->get('/contacts/create')
    ->assertStatus(200)
    ->assertInertia(fn (Assert $assert) => $assert
      ->component('Contacts/Create')
      ->has('organizations', 1)
      ->has('organizations.0', fn (Assert $assert) => $assert
        ->where('name', 'Example Organization Inc.')
        ->has('id')
      )
    );
});

test('can create contact', function () {
  $organization = $this->user->account->organizations()->first();

  $this->actingAs($this->user)
    ->post('/contacts', [
      'first_name' => 'Bob',
      'last_name' => 'Johnson',
      'organization_id' => $organization->id,
      'email' => 'bob@example.com',
      'phone' => '555-999-8888',
      'address' => '123 Main St',
      'city' => 'Anytown',
      'region' => 'CA',
      'country' => 'US',
      'postal_code' => '12345',
    ])
    ->assertRedirect('/contacts')
    ->assertSessionHas('success', 'Contact created.');

  $this->assertDatabaseHas('contacts', [
    'first_name' => 'Bob',
    'last_name' => 'Johnson',
    'email' => 'bob@example.com',
    'phone' => '555-999-8888',
    'account_id' => $this->user->account_id,
  ]);
});

test('can create contact without organization', function () {
  $this->actingAs($this->user)
    ->post('/contacts', [
      'first_name' => 'Bob',
      'last_name' => 'Johnson',
      'email' => 'bob@example.com',
    ])
    ->assertRedirect('/contacts')
    ->assertSessionHas('success', 'Contact created.');

  $this->assertDatabaseHas('contacts', [
    'first_name' => 'Bob',
    'last_name' => 'Johnson',
    'organization_id' => null,
  ]);
});

test('validates required fields when creating contact', function () {
  $this->actingAs($this->user)
    ->post('/contacts', [])
    ->assertSessionHasErrors(['first_name', 'last_name']);
});

test('validates organization exists when creating contact', function () {
  $this->actingAs($this->user)
    ->post('/contacts', [
      'first_name' => 'Bob',
      'last_name' => 'Johnson',
      'organization_id' => 999, // Non-existent organization
    ])
    ->assertSessionHasErrors('organization_id');
});

test('can view edit contact page', function () {
  $contact = $this->user->account->contacts()->first();

  $this->actingAs($this->user)
    ->get("/contacts/{$contact->id}/edit")
    ->assertStatus(200)
    ->assertInertia(fn (Assert $assert) => $assert
      ->component('Contacts/Edit')
      ->has('contact', fn (Assert $assert) => $assert
        ->where('id', $contact->id)
        ->where('first_name', 'Martin')
        ->where('last_name', 'Abbott')
        ->where('email', 'martin.abbott@example.com')
        ->has('organization_id')
        ->has('phone')
        ->has('address')
        ->has('city')
        ->has('region')
        ->has('country')
        ->has('postal_code')
        ->has('deleted_at')
      )
      ->has('organizations', 1)
    );
});

test('can update contact', function () {
  $contact = $this->user->account->contacts()->first();

  $this->actingAs($this->user)
    ->put("/contacts/{$contact->id}", [
      'first_name' => 'Martin',
      'last_name' => 'Updated',
      'email' => 'martin.updated@example.com',
      'phone' => '555-111-9999',
      'address' => '456 Updated St',
      'city' => 'Updated City',
      'region' => 'NY',
      'country' => 'US',
      'postal_code' => '54321',
    ])
    ->assertRedirect()
    ->assertSessionHas('success', 'Contact updated.');

  $this->assertDatabaseHas('contacts', [
    'id' => $contact->id,
    'first_name' => 'Martin',
    'last_name' => 'Updated',
    'email' => 'martin.updated@example.com',
    'phone' => '555-111-9999',
  ]);
});

test('can delete contact', function () {
  $contact = $this->user->account->contacts()->first();

  $this->actingAs($this->user)
    ->delete("/contacts/{$contact->id}")
    ->assertRedirect()
    ->assertSessionHas('success', 'Contact deleted.');

  $this->assertSoftDeleted('contacts', ['id' => $contact->id]);
});

test('can restore deleted contact', function () {
  $contact = $this->user->account->contacts()->first();
  $contact->delete();

  $this->actingAs($this->user)
    ->put("/contacts/{$contact->id}/restore")
    ->assertRedirect()
    ->assertSessionHas('success', 'Contact restored.');

  $this->assertDatabaseHas('contacts', ['id' => $contact->id, 'deleted_at' => null]);
});
