<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardService $dashboard): View
    {
        $this->authorize('accessAdmin', User::class);

        $stats = $dashboard->stats();

        return view('admin.dashboard', [
            'user' => $request->user(),
            'stats' => $stats,
            'charts' => $dashboard->chartPayload($stats),
        ]);
    }
}
