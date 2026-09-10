<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;
use App\Mail\InvoiceMail;
use Illuminate\Support\Facades\Mail;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Invoice::class);

        /** @var User $user */
        $user = $request->user();

        return $user->invoices()->with('client', 'items')->get();
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

        try {
            $client = $request->user()->clients()->findOrFail($validated['client_id']);

            $invoice = $request->user()->invoices()->create([
                'client_id'      => $client->id,
                'invoice_number' => $validated['invoice_number'],
                'issue_date'     => $validated['issue_date'],
                'due_date'       => $validated['due_date'],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Client not found or does not belong to the user.'], 404);
        }

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

    public function send(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        if ($invoice->status !== 'draft') {
            return response()->json([
                'message' => 'Only draft invoices can be sent.',
            ], 422);
        }

        $invoice->markAsSent();

         Mail::to($invoice->client->email)->send(new InvoiceMail($invoice));

        return $invoice;
    }

    public function markPaid(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        if ($invoice->status !== 'sent' && $invoice->status !== 'overdue') {
            return response()->json([
                'message' => 'Only sent or overdue invoices can be marked as paid.',
            ], 422);
        }
        $invoice->markAsPaid();

        return $invoice;
    }
}