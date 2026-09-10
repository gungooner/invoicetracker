<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public Invoice $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice->load('client', 'items');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Invoice {$this->invoice->invoice_number} from {$this->invoice->user->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->buildBody(),
        );
    }

    protected function buildBody(): string
    {
        $lines = [];

        $lines[] = "Hi {$this->invoice->client->name},";
        $lines[] = "";
        $lines[] = "You have a new invoice from {$this->invoice->user->name}.";
        $lines[] = "";
        $lines[] = "Invoice Number: {$this->invoice->invoice_number}";
        $lines[] = "Issue Date: {$this->invoice->issue_date->format('F j, Y')}";
        $lines[] = "Due Date: {$this->invoice->due_date->format('F j, Y')}";
        $lines[] = "";
        $lines[] = "Items:";

        foreach ($this->invoice->items as $item) {
            $lineTotal = number_format($item->line_total, 2);
            $unitPrice = number_format($item->unit_price, 2);
            $lines[] = "- {$item->description} (Qty: {$item->quantity} x \${$unitPrice} = \${$lineTotal})";
        }

        $lines[] = "";
        $lines[] = "Total Due: $" . number_format($this->invoice->total, 2);
        $lines[] = "";
        $lines[] = "Thanks,";
        $lines[] = $this->invoice->user->name;

        return implode('<br>', $lines);
    }
}