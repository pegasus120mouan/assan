<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CouponRequest;
use App\Models\Coupon;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Coupon::class);

        $coupons = Coupon::query()
            ->when($request->filled('q'), fn ($query) => $query->where('code', 'like', '%'.$request->string('q').'%'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.coupons.index', [
            'coupons' => $coupons,
            'filters' => $request->only(['q']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Coupon::class);

        return view('admin.coupons.create');
    }

    public function store(CouponRequest $request, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('create', Coupon::class);
        $coupon = Coupon::query()->create($request->couponData());
        $audit->record('Coupon créé', 'coupons', $coupon, null, $coupon->toArray());

        return redirect()->route('admin.coupons.index')->with('status', 'Coupon créé.');
    }

    public function edit(Coupon $coupon): View
    {
        $this->authorize('update', $coupon);

        return view('admin.coupons.edit', ['coupon' => $coupon]);
    }

    public function update(CouponRequest $request, Coupon $coupon, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('update', $coupon);
        $old = $coupon->toArray();
        $coupon->update($request->couponData());
        $audit->record('Coupon modifié', 'coupons', $coupon, $old, $coupon->fresh()?->toArray());

        return redirect()->route('admin.coupons.index')->with('status', 'Coupon mis à jour.');
    }

    public function destroy(Coupon $coupon, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('delete', $coupon);
        $old = $coupon->toArray();
        $coupon->delete();
        $audit->record('Coupon supprimé', 'coupons', null, $old);

        return redirect()->route('admin.coupons.index')->with('status', 'Coupon supprimé.');
    }
}
