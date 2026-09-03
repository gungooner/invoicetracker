<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::create('invoices', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('client_id')->constrained()->cascadeOnDelete();
        $table->string('invoice_number')->unique();
        $table->enum('status', ['draft', 'sent', 'paid', 'overdue'])->default('draft');
        $table->date('issue_date');
        $table->date('due_date');
        $table->decimal('total', 10, 2)->default(0);
        $table->timestamp('sent_at')->nullable();
        $table->timestamp('paid_at')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
