<?php

namespace App\Http\Controllers\Storefront\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('storefront.account.dashboard', [
            'user' => $request->user(),
        ]);
    }
}
