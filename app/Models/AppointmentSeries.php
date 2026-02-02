<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * AppointmentSeries model representing a recurring appointment pattern.
 * 
 * This model defines a series of recurring appointments for a customer.
 * Each series has associated weekday slots that define when appointments occur.
 *
 * @property int $id
 * @property int $customer_id
 * @property string $title
 * @property string|null $notes
 * @property string $timezone
 * @property \Illuminate\Support\Carbon $starts_on
 * @property \Illuminate\Support\Carbon|null $ends_on
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class AppointmentSeries extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'customer_id',
        'title',
        'notes',
        'timezone',
        'starts_on',
        'ends_on',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'starts_on' => 'date',
        'ends_on' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get all appointments time slots for this appointment series.
     *
     * @return HasMany Relationship to Appointment model
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'series_id');
    }

    /**
     * Get all weekday time slots for this appointment series.
     *
     * @return HasMany Relationship to AppointmentSeriesWeekday model
     */
    public function weekdays(): HasMany
    {
        return $this->hasMany(AppointmentSeriesWeekday::class, 'series_id');
    }

    /**
     * Get the customer that owns this appointment series.
     *
     * @return BelongsTo Relationship to Customer model
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
