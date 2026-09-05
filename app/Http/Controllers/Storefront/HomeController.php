<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\Storefront\CatalogService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(private readonly CatalogService $catalog) {}

    public function __invoke(): View
    {
        return view('storefront.home', $this->catalog->homeSections());
    }
}
