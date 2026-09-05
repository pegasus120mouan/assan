<?php

namespace App\Http\Controllers\Storefront\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\Auth\LoginRequest;
use App\Services\Storefront\CartService;
use App\Support\AuthRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('storefront.auth.login');
    }

    public function store(LoginRequest $request, CartService $carts): RedirectResponse
    {
        $guestCartId = $request->session()->get('cart_id');
        $request->authenticate();
        $carts->mergeGuestCartIntoUser($request->user(), $guestCartId ? (int) $guestCartId : null);
        $request->session()->regenerate();

        return AuthRedirect::for($request->user());
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
