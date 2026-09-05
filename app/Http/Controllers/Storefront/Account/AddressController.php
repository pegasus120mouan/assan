<?php

namespace App\Http\Controllers\Storefront\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\Account\AddressRequest;
use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AddressController extends Controller
{
    public function index(Request $request): View
    {
        return view('storefront.account.addresses.index', [
            'addresses' => $request->user()->addresses()->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('storefront.account.addresses.form', [
            'address' => null,
        ]);
    }

    public function store(AddressRequest $request): RedirectResponse
    {
        $address = $request->user()->addresses()->create($request->validated());
        $this->syncDefault($request, $address);

        return redirect()->route('account.addresses.index')->with('status', 'Adresse enregistrée.');
    }

    public function edit(Request $request, Address $address): View
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        return view('storefront.account.addresses.form', [
            'address' => $address,
        ]);
    }

    public function update(AddressRequest $request, Address $address): RedirectResponse
    {
        abort_unless($address->user_id === $request->user()->id, 403);
        $address->update($request->validated());
        $this->syncDefault($request, $address);

        return redirect()->route('account.addresses.index')->with('status', 'Adresse mise à jour.');
    }

    public function destroy(Request $request, Address $address): RedirectResponse
    {
        abort_unless($address->user_id === $request->user()->id, 403);
        $address->delete();

        return back()->with('status', 'Adresse supprimée.');
    }

    private function syncDefault(Request $request, Address $address): void
    {
        if (! $address->is_default) {
            return;
        }

        $request->user()->addresses()->whereKeyNot($address->id)->update(['is_default' => false]);
    }
}
