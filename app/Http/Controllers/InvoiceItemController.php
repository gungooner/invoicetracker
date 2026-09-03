<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;

class InvoiceItemController extends Controller
{
    public function store(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $validated = $request->validate([
            'description' => 'required|string',
            'quantity'    => 'required|integer|min:1',
            'unit_price'  => 'required|numeric|min:0',
        ]);

        $item = $invoice->items()->create([
            'description' => $validated['description'],
            'quantity'    => $validated['quantity'],
            'unit_price'  => $validated['unit_price'],
            'line_total'  => $validated['quantity'] * $validated['unit_price'],
        ]);

        $invoice->recalculateTotal();

        return response()->json($item, 201);
    }

    public function update(Request $request, InvoiceItem $invoiceItem)
    {
        $this->authorize('update', $invoiceItem);

        $validated = $request->validate([
            'description' => 'sometimes|required|string',
            'quantity'    => 'sometimes|required|integer|min:1',
            'unit_price'  => 'sometimes|required|numeric|min:0',
        ]);

        $invoiceItem->fill($validated);
        $invoiceItem->line_total = $invoiceItem->quantity * $invoiceItem->unit_price;
        $invoiceItem->save();

        $invoiceItem->invoice->recalculateTotal();

        return $invoiceItem;
    }

    public function destroy(Request $request, InvoiceItem $invoiceItem)
    {
        $this->authorize('delete', $invoiceItem);

        $invoice = $invoiceItem->invoice;
        $invoiceItem->delete();
        $invoice->recalculateTotal();

        return response()->json(null, 204);
    }
}