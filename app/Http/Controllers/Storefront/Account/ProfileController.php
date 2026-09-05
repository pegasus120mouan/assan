<?php

namespace App\Http\Controllers\Storefront\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\Account\ProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->load('profile');

        return view('storefront.account.profile', [
            'user' => $user,
        ]);
    }

    public function update(ProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->forceFill($request->safe()->only(['name', 'email', 'phone']))->save();

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'address' => $request->validated('address'),
                'commune' => $request->validated('commune'),
                'city' => $request->validated('city') ?: 'Abidjan',
                'country' => "Côte d'Ivoire",
            ]
        );

        return back()->with('status', 'Profil mis à jour.');
    }
}
