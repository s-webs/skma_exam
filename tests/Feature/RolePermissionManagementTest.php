<?php

use App\Models\ExamType;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->developer = User::factory()->create();
    $this->developer->assignRole('developer');
});

test('developer can edit exam type when ktbo role exists in database', function () {
    $examType = ExamType::create([
        'name_ru' => 'Test Type',
        'slug' => 'test-type',
        'is_active' => true,
    ]);

    $this->actingAs($this->developer)
        ->get(route('admin.exam-types.edit', $examType))
        ->assertOk();
});

test('developer can edit exam type when assignable roles table is empty except developer', function () {
    Role::where('name', '!=', 'developer')->delete();

    $examType = ExamType::create([
        'name_ru' => 'Empty Roles Type',
        'slug' => 'empty-roles-type',
        'is_active' => true,
    ]);

    $this->actingAs($this->developer)
        ->get(route('admin.exam-types.edit', $examType))
        ->assertOk();
});

test('developer can access roles management', function () {
    $this->actingAs($this->developer)
        ->get(route('admin.roles.index'))
        ->assertOk();
});

test('developer can create role with permissions', function () {
    $permission = Permission::where('name', 'exams.view')->first();

    $this->actingAs($this->developer)
        ->post(route('admin.roles.store'), [
            'name' => 'custom-role',
            'permission_names' => [$permission->name],
        ])
        ->assertRedirect(route('admin.roles.index'));

    expect(Role::where('name', 'custom-role')->exists())->toBeTrue();
});

test('user can have multiple roles with merged permissions', function () {
    $user = User::factory()->create();
    $user->assignRole(['ktbo', 'registrator']);

    expect($user->hasRole('ktbo'))->toBeTrue();
    expect($user->hasRole('registrator'))->toBeTrue();
    expect($user->can('questions.view'))->toBeTrue();
    expect($user->can('exam-registrations.approve'))->toBeTrue();
});

test('user without permission cannot access users index', function () {
    $user = User::factory()->create();
    $user->assignRole('registrator');

    $this->actingAs($user)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});
