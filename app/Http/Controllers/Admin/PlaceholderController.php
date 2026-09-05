<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlaceholderController extends Controller
{
    /**
     * @var array<string, string>
     */
    private array $titles = [
        'admin.orders.index' => 'Commandes',
        'admin.customers.index' => 'Clients',
        'admin.payments.index' => 'Paiements',
        'admin.deliveries.index' => 'Livraisons',
        'admin.coupons.index' => 'Coupons',
        'admin.promotions.index' => 'Promotions',
        'admin.reviews.index' => 'Avis',
        'admin.settings.index' => 'Paramètres',
    ];

    public function __invoke(Request $request): View
    {
        $this->authorize('accessAdmin', User::class);

        $routeName = (string) $request->route()?->getName();
        $title = $this->titles[$routeName] ?? 'Module';

        return view('admin.placeholder', [
            'title' => $title,
        ]);
    }
}
