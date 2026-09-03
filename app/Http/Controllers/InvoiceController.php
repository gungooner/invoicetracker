<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Invoice::class);

        return $request->user()->invoices()->with('client', 'items')->get();
    }

    public function store(Request $request)
    {
        $this->authorize('create', Invoice::class);

        $validated = $request->validate([
            'client_id'      => 'required|exists:clients,id',
            'invoice_number' => 'required|string|unique:invoices,invoice_number',
            'issue_date'     => 'required|date',
            'due_date'       => 'required|date|after_or_equal:issue_date',
        ]);

        $client = $request->user()->clients()->findOrFail($validated['client_id']);

        $invoice = $request->user()->invoices()->create([
            'client_id'      => $client->id,
            'invoice_number' => $validated['invoice_number'],
            'issue_date'     => $validated['issue_date'],
            'due_date'       => $validated['due_date'],
        ]);

        return response()->json($invoice, 201);
    }

    public function show(Request $request, Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        return $invoice->load('client', 'items');
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $validated = $request->validate([
            'issue_date' => 'sometimes|required|date',
            'due_date'   => 'sometimes|required|date|after_or_equal:issue_date',
        ]);

        $invoice->update($validated);

        return $invoice;
    }

    public function destroy(Request $request, Invoice $invoice)
    {
        $this->authorize('delete', $invoice);

        $invoice->delete();

        return response()->json(null, 204);
    }
}