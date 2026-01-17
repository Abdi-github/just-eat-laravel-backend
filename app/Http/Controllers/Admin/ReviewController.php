<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Review\Services\ReviewService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    public function __construct(private readonly ReviewService $service) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'visible']);
        $reviews = $this->service->paginateAll($filters);

        return Inertia::render('Review/Index', [
            'reviews' => $reviews,
            'filters' => $filters,
        ]);
    }

    public function show(int $id): Response
    {
        $review = $this->service->findByIdWithDetails($id);

        abort_unless($review, 404);

        return Inertia::render('Review/Show', [
            'review' => $review,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'is_visible' => ['boolean'],
        ]);

        $this->service->update($id, $validated);

        return back()->with('success', 'Review updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->service->delete($id);

        return redirect()->route('admin.reviews.index')->with('success', 'Review deleted.');
    }
}
