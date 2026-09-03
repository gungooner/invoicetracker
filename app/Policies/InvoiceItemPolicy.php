<?php

namespace App\Policies;

use App\Models\InvoiceItem;
use App\Models\User;

class InvoiceItemPolicy
{
    public function view(User $user, InvoiceItem $invoiceItem): bool
    {
        return $user->id === $invoiceItem->invoice->user_id;
    }

    public function update(User $user, InvoiceItem $invoiceItem): bool
    {
        return $user->id === $invoiceItem->invoice->user_id;
    }

    public function delete(User $user, InvoiceItem $invoiceItem): bool
    {
        return $user->id === $invoiceItem->invoice->user_id;
    }
}