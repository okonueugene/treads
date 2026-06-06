<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class VendorApprovalController extends Controller
{
    public function index(Request $request)
    {
        $auth = $request->user();
        if (! $auth || ! method_exists($auth, 'hasRole') || (! $auth->hasRole('Admin') && ! $auth->hasRole('admin'))) {
            abort(403);
        }

        $vendors = User::query()
            ->where('is_vendor', true)
            ->where('is_vendor_approved', false)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.vendor_approvals', compact('vendors'));
    }

    public function approve(Request $request, User $user)
    {
        $auth = $request->user();
        if (! $auth || ! method_exists($auth, 'hasRole') || (! $auth->hasRole('Admin') && ! $auth->hasRole('admin'))) {
            abort(403);
        }

        if (! $user->is_vendor) {
            return back()->with('status', 'User is not a vendor.');
        }

        $user->update(['is_vendor_approved' => true, 'vendor_approved_at' => now()]);

        // assign 'vendor' role if Spatie exists and role is available
        try {
            if (method_exists($user, 'assignRole')) {
                // Prefer exact role names seeded (Vendor or vendor)
                if (\Spatie\Permission\Models\Role::where('name', 'Vendor')->exists()) {
                    $user->assignRole('Vendor');
                } elseif (\Spatie\Permission\Models\Role::where('name', 'vendor')->exists()) {
                    $user->assignRole('vendor');
                } else {
                    // fallback to creating/assigning vendor role
                    $user->assignRole('Vendor');
                }
            }
        } catch (\Throwable $e) {
            // ignore assignment errors
        }

        // Send notification to vendor
        try {
            if (method_exists($user, 'notify')) {
                $user->notify(new \App\Notifications\VendorApproved());
            }
        } catch (\Throwable $e) {
            // ignore notification failures
        }

        return back()->with('status', 'Vendor approved.');
    }

    public function reject(Request $request, User $user)
    {
        $auth = $request->user();
        if (! $auth || ! method_exists($auth, 'hasRole') || (! $auth->hasRole('Admin') && ! $auth->hasRole('admin'))) {
            abort(403);
        }

        if (! $user->is_vendor) {
            return back()->with('status', 'User is not a vendor.');
        }

        $reason = $request->input('reason');
        $user->update([
            'is_vendor_approved' => false,
            'vendor_rejection_reason' => $reason,
        ]);

        // Send rejection notification
        try {
            if (method_exists($user, 'notify')) {
                $user->notify(new \App\Notifications\VendorRejected($reason));
            }
        } catch (\Throwable $e) {
            // ignore notification failures
        }

        return back()->with('status', 'Vendor rejected.');
    }
}
