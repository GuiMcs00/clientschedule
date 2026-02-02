<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Appointment model representing an individual appointment instance.
 * 
 * This model handles both standalone appointments and appointments generated from recurring series.
 * Includes constraints to prevent overlapping appointments for the same customer.
 *
 * @property int $id
 * @property int $customer_id
 * @property int|null $series_id
 * @property string $title
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon $starts_at
 * @property \Illuminate\Support\Carbon $ends_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Appointment extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'customer_id',
        'series_id',
        'title',
        'notes',
        'starts_at',
        'ends_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Get the customer that owns this appointment.
     *
     * @return BelongsTo Relationship to Customer model
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get the series that owns this appointment.
     *
     * @return BelongsTo Relationship to AppointmentSeries model
     */
    public function series(): BelongsTo
    {
        return $this->belongsTo(AppointmentSeries::class, 'series_id');
    }
}
