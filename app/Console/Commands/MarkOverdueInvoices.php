<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;

class MarkOverdueInvoices extends Command
{
    protected $signature = 'invoices:mark-overdue';

    protected $description = 'Mark sent invoices as overdue if their due date has passed';

    public function handle(): void
    {
        $count = Invoice::where('status', 'sent')
            ->where('due_date', '<', now())
            ->update(['status' => 'overdue']);

        $this->info("Marked {$count} invoice(s) as overdue.");
    }
}