<?php

return [
    'navigation' => [
        [
            'label' => 'Tableau de bord',
            'route' => 'admin.dashboard',
            'group' => 'Vue d\'ensemble',
        ],
        [
            'label' => 'Commandes',
            'route' => 'admin.orders.index',
            'group' => 'Ventes',
            'badge' => 'pending_orders',
        ],
        [
            'label' => 'Paiements',
            'route' => 'admin.payments.index',
            'group' => 'Ventes',
        ],
        [
            'label' => 'Livraisons',
            'route' => 'admin.deliveries.index',
            'group' => 'Ventes',
        ],
        [
            'label' => 'Frais de livraison',
            'route' => 'admin.delivery-fees.index',
            'group' => 'Ventes',
        ],
        [
            'label' => 'Produits',
            'route' => 'admin.products.index',
            'group' => 'Catalogue',
        ],
        [
            'label' => 'Catégories',
            'route' => 'admin.categories.index',
            'group' => 'Catalogue',
        ],
        [
            'label' => 'Marques',
            'route' => 'admin.brands.index',
            'group' => 'Catalogue',
        ],
        [
            'label' => 'Stocks',
            'route' => 'admin.stock.index',
            'group' => 'Catalogue',
        ],
        [
            'label' => 'Clients',
            'route' => 'admin.customers.index',
            'group' => 'Clients',
        ],
        [
            'label' => 'Avis',
            'route' => 'admin.reviews.index',
            'group' => 'Clients',
        ],
        [
            'label' => 'Coupons',
            'route' => 'admin.coupons.index',
            'group' => 'Marketing',
        ],
        [
            'label' => 'Promotions',
            'route' => 'admin.promotions.index',
            'group' => 'Marketing',
        ],
        [
            'label' => 'Utilisateurs',
            'route' => 'admin.users.index',
            'group' => 'Système',
            'admin_only' => true,
        ],
        [
            'label' => 'Paramètres',
            'route' => 'admin.settings.index',
            'group' => 'Système',
        ],
    ],
];
