<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Create the appointment_series table.
     * 
     * This table stores recurring appointment series configurations.
     * Each series defines a pattern of recurring appointments for a customer.
     */
    public function up(): void
    {
        Schema::create('appointment_series', function (Blueprint $table) {
            $table->id();

            // Foreign key to customers - cascade delete if customer is deleted
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            // Series identification
            $table->string('title', 150);
            $table->text('notes')->nullable();

            // Timezone for this series (important for recurring appointments)
            $table->string('timezone', 64)->default(config('app.timezone'));

            // Date range for the series (when it starts and optionally when it ends)
            $table->date('starts_on');
            $table->date('ends_on')->nullable(); // null = indefinite series

            // Whether this series is currently active (can be toggled without deletion)
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // Index for efficiently querying customer's active series
            $table->index(['customer_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_series');
    }
};
