<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Create the appointments table.
     * 
     * This table stores individual appointment instances (both standalone and recurring).
     * Includes PostgreSQL-specific constraints to prevent overlapping appointments.
     */
    public function up(): void
    {
        // Enable btree_gist extension for exclusion constraints on timestamp ranges
        DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist');

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            // Foreign key to customers - cascade delete if customer is deleted
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            // Optional reference to parent series (null for standalone appointments)
            // Set to null if series is deleted, keeping the appointment orphaned
            $table->foreignId('series_id')
                ->nullable()
                ->constrained('appointment_series')
                ->nullOnDelete();

            // Appointment details
            $table->string('title', 150);
            $table->text('notes')->nullable();

            // Timestamp with timezone for accurate scheduling across timezones
            $table->timestampTz('starts_at');
            $table->timestampTz('ends_at');

            $table->timestamps();
            $table->softDeletes();

            // Indexes for efficiently querying appointments by customer and time
            $table->index(['customer_id', 'starts_at']);
            $table->index(['series_id', 'starts_at']);
        });

        // PostgreSQL constraint: End time must be after start time
        DB::statement("
            ALTER TABLE appointments
            ADD CONSTRAINT chk_appointments_time_order
            CHECK (ends_at > starts_at)
        ");

        // PostgreSQL exclusion constraint: Prevent overlapping appointments for the same customer
        // Uses tstzrange to check if time ranges overlap (&&)
        // Only applies to non-deleted appointments (WHERE deleted_at IS NULL)
        DB::statement("
            ALTER TABLE appointments
            ADD CONSTRAINT appointments_no_overlap
            EXCLUDE USING gist (
                customer_id WITH =,
                tstzrange(starts_at, ends_at, '[)') WITH &&
            )
            WHERE (deleted_at IS NULL)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
