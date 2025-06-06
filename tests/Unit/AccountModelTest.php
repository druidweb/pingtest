<?php

use App\Models\Account;
use App\Models\Contact;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('account has many users', function () {
  $account = Account::create(['name' => 'Test Account']);
  $user1 = User::factory()->create(['account_id' => $account->id]);
  $user2 = User::factory()->create(['account_id' => $account->id]);

  expect($account->users)->toHaveCount(2);
  expect($account->users->first())->toBeInstanceOf(User::class);
  expect($account->users->pluck('id'))->toContain($user1->id, $user2->id);
});

test('account has many organizations', function () {
  $account = Account::create(['name' => 'Test Account']);
  $org1 = Organization::factory()->create(['account_id' => $account->id]);
  $org2 = Organization::factory()->create(['account_id' => $account->id]);

  expect($account->organizations)->toHaveCount(2);
  expect($account->organizations->first())->toBeInstanceOf(Organization::class);
  expect($account->organizations->pluck('id'))->toContain($org1->id, $org2->id);
});

test('account has many contacts', function () {
  $account = Account::create(['name' => 'Test Account']);
  $contact1 = Contact::factory()->create(['account_id' => $account->id]);
  $contact2 = Contact::factory()->create(['account_id' => $account->id]);

  expect($account->contacts)->toHaveCount(2);
  expect($account->contacts->first())->toBeInstanceOf(Contact::class);
  expect($account->contacts->pluck('id'))->toContain($contact1->id, $contact2->id);
});
