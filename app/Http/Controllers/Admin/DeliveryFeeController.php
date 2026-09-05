<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeliveryFeeRequest;
use App\Models\City;
use App\Models\Commune;
use App\Models\DeliveryFee;
use App\Models\Zone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryFeeController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', DeliveryFee::class);

        $fees = DeliveryFee::query()
            ->with(['city', 'commune', 'zone'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $editingId = (int) (old('_edit_modal') ?: $request->integer('edit'));
        $editingFee = $editingId > 0
            ? DeliveryFee::query()->with(['city', 'commune', 'zone'])->find($editingId)
            : null;

        $modalFees = $fees->getCollection();
        if ($editingFee && ! $modalFees->contains('id', $editingFee->id)) {
            $modalFees = $modalFees->concat([$editingFee]);
        }

        $initialModal = null;
        if (old('_create_modal') || $request->boolean('create')) {
            $initialModal = 'create';
        } elseif ($editingFee) {
            $initialModal = $editingFee->id;
        }

        return view('admin.delivery-fees.index', array_merge($this->formData(), [
            'fees' => $fees,
            'modalFees' => $modalFees,
            'initialModal' => $initialModal,
        ]));
    }

    public function create(): RedirectResponse
    {
        $this->authorize('create', DeliveryFee::class);

        return redirect()->route('admin.delivery-fees.index', ['create' => 1]);
    }

    public function store(DeliveryFeeRequest $request): RedirectResponse
    {
        $this->authorize('create', DeliveryFee::class);
        DeliveryFee::query()->create($request->feeData());

        return redirect()->route('admin.delivery-fees.index')->with('status', 'Tarif de livraison créé.');
    }

    public function edit(DeliveryFee $deliveryFee): RedirectResponse
    {
        $this->authorize('update', $deliveryFee);

        return redirect()->route('admin.delivery-fees.index', ['edit' => $deliveryFee->id]);
    }

    public function update(DeliveryFeeRequest $request, DeliveryFee $deliveryFee): RedirectResponse
    {
        $this->authorize('update', $deliveryFee);
        $deliveryFee->update($request->feeData());

        return redirect()->route('admin.delivery-fees.index')->with('status', 'Tarif de livraison mis à jour.');
    }

    public function destroy(DeliveryFee $deliveryFee): RedirectResponse
    {
        $this->authorize('delete', $deliveryFee);
        $deliveryFee->delete();

        return redirect()->route('admin.delivery-fees.index')->with('status', 'Tarif de livraison supprimé.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'cities' => City::query()->orderBy('name')->get(),
            'communes' => Commune::query()->orderBy('name')->get(),
            'zones' => Zone::query()->orderBy('name')->get(),
        ];
    }
}
