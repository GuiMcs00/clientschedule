<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Create the appointment_series_weekdays table.
     * 
     * This table defines the specific weekdays and times for a recurring appointment series.
     * A series can have multiple weekday slots (e.g., Mon 9-10am, Wed 2-3pm, Fri 9-10am).
     */
    public function up(): void
    {
        Schema::create('appointment_series_weekdays', function (Blueprint $table) {
            $table->id();

            // Foreign key to appointment series - cascade delete when series is deleted
            $table->foreignId('series_id')
                ->constrained('appointment_series')
                ->cascadeOnDelete();

            // Weekday number: 0 (Sunday) to 6 (Saturday)
            $table->unsignedSmallInteger('weekday');

            // Time slot for this weekday (times are timezone-agnostic)
            $table->time('start_time');
            $table->time('end_time');

            $table->timestamps();

            // Prevent duplicate time slots for the same series/weekday
            $table->unique(['series_id', 'weekday', 'start_time', 'end_time'], 'series_weekday_slot_unique');

            // Index for querying slots by series and weekday
            $table->index(['series_id', 'weekday']);
        });

        // PostgreSQL constraint: Ensure weekday is in valid range (0-6)
        DB::statement("
            ALTER TABLE appointment_series_weekdays
            ADD CONSTRAINT chk_series_weekday_range
            CHECK (weekday >= 0 AND weekday <= 6)
        ");

        // PostgreSQL constraint: End time must be after start time
        DB::statement("
            ALTER TABLE appointment_series_weekdays
            ADD CONSTRAINT chk_series_time_order
            CHECK (end_time > start_time)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_series_weekdays');
    }
};
