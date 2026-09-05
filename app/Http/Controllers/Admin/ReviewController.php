<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\Storefront\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Review::class);

        $reviews = Review::query()
            ->with(['product', 'user'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reviews.index', [
            'reviews' => $reviews,
            'filters' => $request->only(['status']),
        ]);
    }

    public function update(Request $request, Review $review, ReviewService $reviews): RedirectResponse
    {
        $this->authorize('update', $review);

        $status = ReviewStatus::from($request->validate([
            'status' => ['required', Rule::enum(ReviewStatus::class)],
        ])['status']);

        $reviews->moderate($review, $status);

        return back()->with('status', 'Avis mis à jour.');
    }
}
