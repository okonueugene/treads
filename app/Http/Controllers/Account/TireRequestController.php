<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TireRequestController extends Controller
{
    public function index(Request $request): View
    {
        $requests = $request->user()
            ->tireRequests()
            ->latest()
            ->paginate(10);

        return view('account.tire-requests.index', compact('requests'));
    }
}
