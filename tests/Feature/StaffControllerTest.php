<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StaffControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_form_offers_secretary_instead_of_warehouse(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.staff.create'))
            ->assertOk()
            ->assertSee('secretary')
            ->assertDontSee('warehouse');
    }

    public function test_admin_can_create_a_secretary_account(): void
    {
        $admin = $this->userWithRole('admin');
        Role::findOrCreate('secretary');

        $this->actingAs($admin)
            ->post(route('admin.staff.store'), [
                'name' => 'Pat Secretary',
                'email' => 'pat@example.com',
                'contact' => '09170000000',
                'role' => 'secretary',
                'password' => 'password',
                'is_active' => '1',
            ])
            ->assertRedirectToRoute('admin.staff.index')
            ->assertSessionHas('status', 'Account created.');

        $staff = User::query()->where('email', 'pat@example.com')->first();

        $this->assertNotNull($staff);
        $this->assertTrue($staff->hasRole('secretary'));
        $this->assertTrue($staff->is_active);
    }

    public function test_rejects_warehouse_as_a_staff_role(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)
            ->from(route('admin.staff.create'))
            ->post(route('admin.staff.store'), [
                'name' => 'Old Warehouse',
                'email' => 'warehouse@example.com',
                'role' => 'warehouse',
                'password' => 'password',
            ])
            ->assertRedirectToRoute('admin.staff.create')
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'warehouse@example.com']);
    }

    public function test_admin_can_deactivate_a_staff_account(): void
    {
        $admin = $this->userWithRole('admin');
        $cashier = $this->userWithRole('cashier');

        $this->actingAs($admin)
            ->from(route('admin.staff.index'))
            ->delete(route('admin.staff.destroy', $cashier))
            ->assertRedirectToRoute('admin.staff.index')
            ->assertSessionHas('status', 'Account deactivated.');

        $this->assertFalse($cashier->fresh()->is_active);
    }

    public function test_cashier_cannot_deactivate_a_staff_account(): void
    {
        $cashier = $this->userWithRole('cashier');
        $other = $this->userWithRole('secretary');

        $this->actingAs($cashier)
            ->delete(route('admin.staff.destroy', $other))
            ->assertForbidden();

        $this->assertTrue($other->fresh()->is_active);
    }

    public function test_unauthenticated_request_redirects_to_login(): void
    {
        $cashier = $this->userWithRole('cashier');

        $this->from(route('login'))
            ->delete(route('admin.staff.destroy', $cashier))
            ->assertRedirectToRoute('login');

        $this->assertTrue($cashier->fresh()->is_active);
    }

    private function userWithRole(string $role): User
    {
        Role::findOrCreate($role);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
