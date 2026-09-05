<?php

namespace App\Http\Controllers\Storefront\Auth;

use App\Enums\Status;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\Auth\RegisterUserRequest;
use App\Models\User;
use App\Services\Storefront\CartService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('storefront.auth.register');
    }

    public function store(RegisterUserRequest $request, CartService $carts): RedirectResponse
    {
        $guestCartId = $request->session()->get('cart_id');

        $user = new User;
        $user->forceFill([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'password' => $request->validated('password'),
            'role' => UserRole::Customer,
            'status' => Status::Active,
        ])->save();

        $user->profile()->create([
            'city' => 'Abidjan',
            'country' => "Côte d'Ivoire",
        ]);

        event(new Registered($user));
        Auth::login($user);
        $carts->mergeGuestCartIntoUser($user, $guestCartId ? (int) $guestCartId : null);
        $request->session()->regenerate();

        return redirect()->route('account.dashboard');
    }
}
