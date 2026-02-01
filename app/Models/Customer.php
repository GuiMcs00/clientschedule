<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Customer model representing a customer entity.
 * 
 * This model handles customer data with soft delete functionality.
 * Customers can be filtered by active (not deleted) or inactive (soft deleted) status.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Customer extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
    ];

    /**
     * Get all appointments for this customer.
     *
     * @return HasMany Relationship to Appointment model
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get all appointment series for this customer.
     *
     * @return HasMany Relationship to AppointmentSeries model
     */
    public function series(): HasMany
    {
        return $this->hasMany(AppointmentSeries::class);
    }

    /**
     * Scope a query to only include active (non-deleted) customers.
     *
     * @param Builder $query The query builder instance
     * @return Builder The modified query builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Scope a query to only include inactive (soft deleted) customers.
     *
     * @param Builder $query The query builder instance
     * @return Builder The modified query builder
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->whereNotNull('deleted_at');
    }

}