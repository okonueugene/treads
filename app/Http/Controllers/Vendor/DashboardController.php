<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $vendor = $request->user();

        abort_unless($vendor->is_vendor, 403);

        return view('vendor.dashboard', [
            'products' => $vendor->products()->with('brand')->latest()->get(),
            'recentItems' => $vendor->orderItems()->with('order')->latest()->limit(10)->get(),
        ]);
    }
}
