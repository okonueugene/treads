<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return view('account.index', [
            'recentOrders' => $user->orders()->latest()->limit(3)->get(),
            'openRequests' => $user->tireRequests()->where('status', 'open')->count(),
            'savedCount' => collect(session('saved_products', []))->count(),
        ]);
    }
}
