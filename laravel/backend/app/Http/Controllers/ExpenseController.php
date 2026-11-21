<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Expenses — money going out: security guard wages, caretaker payments,
 * maintenance invoices, utility top-ups, etc.
 *
 * Every write is scoped to the authenticated owner via the BelongsToOwner
 * global scope on the Expense model.
 */
class ExpenseController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Expense::class);

        $perPage = max(5, min((int) $request->integer('per_page', 15), 100));

        $expenses = Expense::query()
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('date', '>=', $request->string('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('date', '<=', $request->string('to')))
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Expenses/Index', [
            'expenses' => [
                'data' => ExpenseResource::collection($expenses->items())->resolve(),
                'links' => [
                    'first' => $expenses->url(1),
                    'last' => $expenses->url($expenses->lastPage()),
                    'prev' => $expenses->previousPageUrl(),
                    'next' => $expenses->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $expenses->currentPage(),
                    'from' => $expenses->firstItem(),
                    'last_page' => $expenses->lastPage(),
                    'per_page' => $expenses->perPage(),
                    'to' => $expenses->lastItem(),
                    'total' => $expenses->total(),
                ],
            ],
            'filters' => [
                'category' => $request->string('category')->toString() ?: null,
                'from' => $request->string('from')->toString() ?: null,
                'to' => $request->string('to')->toString() ?: null,
            ],
            'flash' => ['success' => $request->session()->get('success')],
        ]);
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $expense = Expense::create($request->expenseAttributes());

        return back()->with(
            'success',
            sprintf('Expense of KES %s recorded (%s).', $expense->amount, ucfirst((string) $expense->category)),
        );
    }

    public function update(StoreExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $this->authorize('update', $expense);
        $expense->update($request->expenseAttributes());

        return back()->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $this->authorize('delete', $expense);
        $expense->delete();

        return back()->with('success', 'Expense removed.');
    }
}
