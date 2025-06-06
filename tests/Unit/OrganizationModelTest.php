<?php

use App\Models\Account;
use App\Models\Contact;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
  $this->account = Account::create(['name' => 'Test Account']);
});

test('organization has many contacts', function () {
  $organization = Organization::factory()->create(['account_id' => $this->account->id]);
  $contact1 = Contact::factory()->create([
    'account_id' => $this->account->id,
    'organization_id' => $organization->id,
  ]);
  $contact2 = Contact::factory()->create([
    'account_id' => $this->account->id,
    'organization_id' => $organization->id,
  ]);

  expect($organization->contacts)->toHaveCount(2);
  expect($organization->contacts->first())->toBeInstanceOf(Contact::class);
  expect($organization->contacts->pluck('id'))->toContain($contact1->id, $contact2->id);
});

test('scope filter handles search parameter', function () {
  Organization::factory()->create(['name' => 'Apple Inc.', 'account_id' => $this->account->id]);
  Organization::factory()->create(['name' => 'Microsoft Corp.', 'account_id' => $this->account->id]);
  Organization::factory()->create(['name' => 'Google LLC', 'account_id' => $this->account->id]);

  $results = Organization::filter(['search' => 'Apple'])->get();
  expect($results)->toHaveCount(1);
  expect($results->first()->name)->toBe('Apple Inc.');

  $results = Organization::filter(['search' => 'Corp'])->get();
  expect($results)->toHaveCount(1);
  expect($results->first()->name)->toBe('Microsoft Corp.');

  $results = Organization::filter(['search' => 'LLC'])->get();
  expect($results)->toHaveCount(1);
  expect($results->first()->name)->toBe('Google LLC');
});

test('scope filter handles trashed parameter', function () {
  $org1 = Organization::factory()->create(['account_id' => $this->account->id]);
  $org2 = Organization::factory()->create(['account_id' => $this->account->id]);
  $org2->delete();

  $all = Organization::filter([])->get();
  $withTrashed = Organization::filter(['trashed' => 'with'])->get();
  $onlyTrashed = Organization::filter(['trashed' => 'only'])->get();

  expect($all)->toHaveCount(1);
  expect($withTrashed)->toHaveCount(2);
  expect($onlyTrashed)->toHaveCount(1);
  expect($onlyTrashed->first()->deleted_at)->not->toBeNull();
});

test('resolve route binding includes soft deleted models', function () {
  $organization = Organization::factory()->create(['account_id' => $this->account->id]);
  $organization->delete();

  $resolved = (new Organization)->resolveRouteBinding($organization->id);

  expect($resolved)->not->toBeNull();
  expect($resolved->id)->toBe($organization->id);
  expect($resolved->deleted_at)->not->toBeNull();
});
