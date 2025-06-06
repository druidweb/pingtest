<?php

use App\Models\Account;
use App\Models\Contact;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
  $this->account = Account::create(['name' => 'Test Account']);
  $this->organization = Organization::factory()->create(['account_id' => $this->account->id]);
});

test('contact belongs to organization', function () {
  $contact = Contact::factory()->create([
    'account_id' => $this->account->id,
    'organization_id' => $this->organization->id,
  ]);

  expect($contact->organization)->toBeInstanceOf(Organization::class);
  expect($contact->organization->id)->toBe($this->organization->id);
});

test('contact can exist without organization', function () {
  $contact = Contact::factory()->create([
    'account_id' => $this->account->id,
    'organization_id' => null,
  ]);

  expect($contact->organization)->toBeNull();
});

test('contact name attribute combines first and last name', function () {
  $contact = Contact::factory()->create([
    'account_id' => $this->account->id,
    'first_name' => 'John',
    'last_name' => 'Doe',
  ]);

  expect($contact->name)->toBe('John Doe');
});

test('scope order by name orders by last name then first name', function () {
  Contact::factory()->create(['first_name' => 'John', 'last_name' => 'Zebra', 'account_id' => $this->account->id]);
  Contact::factory()->create(['first_name' => 'Jane', 'last_name' => 'Apple', 'account_id' => $this->account->id]);
  Contact::factory()->create(['first_name' => 'Bob', 'last_name' => 'Apple', 'account_id' => $this->account->id]);

  $contacts = Contact::orderByName()->get();

  expect($contacts->first()->last_name)->toBe('Apple');
  expect($contacts->first()->first_name)->toBe('Bob');
  expect($contacts->last()->last_name)->toBe('Zebra');
});

test('scope filter handles search parameter', function () {
  Contact::factory()->create([
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john@example.com',
    'account_id' => $this->account->id,
  ]);
  Contact::factory()->create([
    'first_name' => 'Jane',
    'last_name' => 'Smith',
    'email' => 'jane@example.com',
    'account_id' => $this->account->id,
  ]);

  $results = Contact::filter(['search' => 'John'])->get();
  expect($results)->toHaveCount(1);
  expect($results->first()->first_name)->toBe('John');

  $results = Contact::filter(['search' => 'Smith'])->get();
  expect($results)->toHaveCount(1);
  expect($results->first()->last_name)->toBe('Smith');

  $results = Contact::filter(['search' => 'jane@example.com'])->get();
  expect($results)->toHaveCount(1);
  expect($results->first()->email)->toBe('jane@example.com');
});

test('scope filter handles trashed parameter', function () {
  $contact1 = Contact::factory()->create(['account_id' => $this->account->id]);
  $contact2 = Contact::factory()->create(['account_id' => $this->account->id]);
  $contact2->delete();

  $all = Contact::filter([])->get();
  $withTrashed = Contact::filter(['trashed' => 'with'])->get();
  $onlyTrashed = Contact::filter(['trashed' => 'only'])->get();

  expect($all)->toHaveCount(1);
  expect($withTrashed)->toHaveCount(2);
  expect($onlyTrashed)->toHaveCount(1);
  expect($onlyTrashed->first()->deleted_at)->not->toBeNull();
});

test('resolve route binding includes soft deleted models', function () {
  $contact = Contact::factory()->create(['account_id' => $this->account->id]);
  $contact->delete();

  $resolved = (new Contact)->resolveRouteBinding($contact->id);

  expect($resolved)->not->toBeNull();
  expect($resolved->id)->toBe($contact->id);
  expect($resolved->deleted_at)->not->toBeNull();
});
