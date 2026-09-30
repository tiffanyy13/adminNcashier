<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StaffRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function index()
    {
        return view('admin.staff.index', ['users' => User::role(['cashier', 'secretary'])->orderBy('name')->paginate(15)]);
    }

    public function create()
    {
        return view('admin.staff.form', ['user' => new User(['is_active' => true])]);
    }

    public function store(StaffRequest $r)
    {
        $u = new User;
        $u->forceFill(['name' => $r->name, 'email' => $r->email, 'contact' => $r->contact,
            'password' => Hash::make($r->password), 'is_active' => $r->boolean('is_active'), 'email_verified_at' => now()])->save();
        $u->assignRole($r->role);

        return redirect()->route('admin.staff.index')->with('status', 'Account created.');
    }

    public function edit(User $staff)
    {
        return view('admin.staff.form', ['user' => $staff]);
    }

    public function update(StaffRequest $r, User $staff)
    {
        $staff->forceFill(['name' => $r->name, 'email' => $r->email, 'contact' => $r->contact, 'is_active' => $r->boolean('is_active')]);
        if ($r->filled('password')) {
            $staff->password = Hash::make($r->password);
        } // password reset
        $staff->save();
        $staff->syncRoles($r->role);

        return redirect()->route('admin.staff.index')->with('status', 'Account updated.');
    }

    public function destroy(User $staff)
    {
        $staff->update(['is_active' => false]);

        return back()->with('status', 'Account deactivated.');
    }
}
