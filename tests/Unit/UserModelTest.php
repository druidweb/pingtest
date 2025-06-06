<?php

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
  $this->account = Account::create(['name' => 'Test Account']);
});

test('user belongs to account', function () {
  $user = User::factory()->create(['account_id' => $this->account->id]);

  expect($user->account)->toBeInstanceOf(Account::class);
  expect($user->account->id)->toBe($this->account->id);
});

test('user name attribute combines first and last name', function () {
  $user = User::factory()->create([
    'account_id' => $this->account->id,
    'first_name' => 'John',
    'last_name' => 'Doe',
  ]);

  expect($user->name)->toBe('John Doe');
});

test('password is hashed when set', function () {
  $user = User::factory()->create(['account_id' => $this->account->id]);
  $plainPassword = 'newpassword123';

  $user->password = $plainPassword;

  expect(Hash::check($plainPassword, $user->password))->toBeTrue();
});

test('password is not rehashed if already hashed', function () {
  $hashedPassword = Hash::make('password123');
  $user = User::factory()->create(['account_id' => $this->account->id]);

  $user->password = $hashedPassword;

  expect($user->password)->toBe($hashedPassword);
});

test('can identify demo user', function () {
  $demoUser = User::factory()->create(['account_id' => $this->account->id, 'email' => 'johndoe@example.com']);
  $regularUser = User::factory()->create(['account_id' => $this->account->id, 'email' => 'other@example.com']);

  expect($demoUser->isDemoUser())->toBeTrue();
  expect($regularUser->isDemoUser())->toBeFalse();
});

test('scope order by name orders by last name then first name', function () {
  User::factory()->create(['account_id' => $this->account->id, 'first_name' => 'John', 'last_name' => 'Zebra']);
  User::factory()->create(['account_id' => $this->account->id, 'first_name' => 'Jane', 'last_name' => 'Apple']);
  User::factory()->create(['account_id' => $this->account->id, 'first_name' => 'Bob', 'last_name' => 'Apple']);

  $users = User::orderByName()->get();

  expect($users->first()->last_name)->toBe('Apple');
  expect($users->first()->first_name)->toBe('Bob');
  expect($users->last()->last_name)->toBe('Zebra');
});

test('scope where role filters by owner status', function () {
  User::factory()->create(['account_id' => $this->account->id, 'owner' => true]);
  User::factory()->create(['account_id' => $this->account->id, 'owner' => false]);
  User::factory()->create(['account_id' => $this->account->id, 'owner' => false]);

  $owners = User::whereRole('owner')->get();
  $users = User::whereRole('user')->get();

  expect($owners)->toHaveCount(1);
  expect($users)->toHaveCount(2);
  expect($owners->first()->owner)->toBeTrue();
  expect($users->first()->owner)->toBeFalse();
});

test('scope filter handles search parameter', function () {
  User::factory()->create(['account_id' => $this->account->id, 'first_name' => 'John', 'last_name' => 'Doe', 'email' => 'john@example.com']);
  User::factory()->create(['account_id' => $this->account->id, 'first_name' => 'Jane', 'last_name' => 'Smith', 'email' => 'jane@example.com']);

  $results = User::filter(['search' => 'John'])->get();
  expect($results)->toHaveCount(1);
  expect($results->first()->first_name)->toBe('John');

  $results = User::filter(['search' => 'Smith'])->get();
  expect($results)->toHaveCount(1);
  expect($results->first()->last_name)->toBe('Smith');

  $results = User::filter(['search' => 'jane@example.com'])->get();
  expect($results)->toHaveCount(1);
  expect($results->first()->email)->toBe('jane@example.com');
});

test('scope filter handles role parameter', function () {
  User::factory()->create(['account_id' => $this->account->id, 'owner' => true]);
  User::factory()->create(['account_id' => $this->account->id, 'owner' => false]);

  $owners = User::filter(['role' => 'owner'])->get();
  $users = User::filter(['role' => 'user'])->get();

  expect($owners)->toHaveCount(1);
  expect($users)->toHaveCount(1);
});

test('scope filter handles trashed parameter', function () {
  $user1 = User::factory()->create(['account_id' => $this->account->id]);
  $user2 = User::factory()->create(['account_id' => $this->account->id]);
  $user2->delete();

  $all = User::filter([])->get();
  $withTrashed = User::filter(['trashed' => 'with'])->get();
  $onlyTrashed = User::filter(['trashed' => 'only'])->get();

  expect($all)->toHaveCount(1);
  expect($withTrashed)->toHaveCount(2);
  expect($onlyTrashed)->toHaveCount(1);
  expect($onlyTrashed->first()->deleted_at)->not->toBeNull();
});

test('resolve route binding includes soft deleted models', function () {
  $user = User::factory()->create(['account_id' => $this->account->id]);
  $user->delete();

  $resolved = (new User)->resolveRouteBinding($user->id);

  expect($resolved)->not->toBeNull();
  expect($resolved->id)->toBe($user->id);
  expect($resolved->deleted_at)->not->toBeNull();
});

test('owner cast works correctly', function () {
  $user = User::factory()->create(['account_id' => $this->account->id, 'owner' => 1]);

  expect($user->owner)->toBeTrue();
  expect($user->owner)->toBeBool();
});

test('email verified at cast works correctly', function () {
  $user = User::factory()->create(['account_id' => $this->account->id, 'email_verified_at' => now()]);

  expect($user->email_verified_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});
