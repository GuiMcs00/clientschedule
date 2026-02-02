<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentSeriesRequest;
use App\Http\Requests\UpdateAppointmentSeriesRequest;
use App\Http\Resources\AppointmentSeriesResource;
use App\Models\Appointment;
use App\Models\AppointmentSeries;
use App\Models\Customer;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Controller for managing customer appointment series.
 * 
 * Handles CRUD operations for recurring appointment series including:
 * - Creating series with weekday time slots
 * - Generating appointment occurrences from series patterns
 * - Updating series and optionally regenerating future occurrences
 * - Deactivating series with optional cleanup of future appointments
 * 
 * Supports PostgreSQL exclusion constraints for conflict detection.
 */
class CustomerAppointmentSeriesController extends Controller
{
    /**
     * List all appointment series for a customer.
     * 
     * Supports filtering by:
     * - status: active (default), inactive, or all
     * - include_weekdays: whether to load weekday slots (default: true)
     *
     * @param Request $request The HTTP request with optional filters
     * @param Customer $customer The customer whose series to retrieve
     * @return JsonResponse Collection of appointment series
     */
    public function index(Request $request, Customer $customer): JsonResponse
    {
        $status = $request->query('status', 'active');

        $query = AppointmentSeries::query()
            ->where('customer_id', $customer->id)
            ->orderByDesc('id');

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        if ($request->boolean('include_weekdays', true)) {
            $query->with('weekdays');
        }

        $series = $query->get();

        return response()->json(AppointmentSeriesResource::collection($series));
    }

    /**
     * Create a new appointment series with weekday slots.
     * 
     * Automatically sets customer_id from the route parameter for security.
     * Optionally generates appointment occurrences immediately.
     * 
     * Query parameters:
     * - generate: whether to generate occurrences (default: false)
     * - weeks: number of weeks to generate (default: 12, max: 52)
     * 
     * Creates the series and weekday slots in a transaction.
     * If generate=true, creates individual appointment records for the pattern.
     *
     * @param StoreAppointmentSeriesRequest $request The validated series data
     * @param Customer $customer The customer for whom to create the series
     * @return JsonResponse The created series or conflict error (409)
     */
    public function store(StoreAppointmentSeriesRequest $request, Customer $customer): JsonResponse
    {
        $data = $request->validated();

        // defaults
        $data['customer_id'] = $customer->id;
        $data['is_active'] = $data['is_active'] ?? true;
        $data['timezone'] = $data['timezone'] ?? config('app.timezone');

        $weekdays = $data['weekdays'] ?? [];
        unset($data['weekdays']);

        $generate = $request->boolean('generate', false);
        $weeks = max(1, min((int) $request->query('weeks', 12), 52));

        try {
            $series = DB::transaction(function () use ($data, $weekdays, $generate, $weeks) {
                $series = AppointmentSeries::create($data);

                $series->weekdays()->createMany($weekdays);

                if ($generate) {
                    $this->generateOccurrences($series, $weeks);
                }

                return $series->load('weekdays');
            });
        } catch (QueryException $e) {
            // Caso você tenha constraint anti-overlap em appointments
            if (str_contains($e->getMessage(), 'appointments_no_overlap')) {
                return response()->json([
                    'message' => 'Conflito de horário: a série gerou ocorrências que colidem com marcações existentes.',
                ], 409);
            }
            throw $e;
        }

        return response()->json(new AppointmentSeriesResource($series), 201);
    }

    /**
     * Get a specific appointment series for a customer.
     * 
     * Verifies that the series belongs to the specified customer.
     * Always includes weekday slot data.
     *
     * @param Customer $customer The customer who owns the series
     * @param AppointmentSeries $series The series to retrieve
     * @return JsonResponse The series details with weekday slots
     */
    public function show(Customer $customer, AppointmentSeries $series): JsonResponse
    {
        $this->assertBelongsToCustomer($customer, $series);

        $series->load('weekdays');

        return response()->json(new AppointmentSeriesResource($series));
    }

    /**
     * Update an appointment series and optionally regenerate occurrences.
     * 
     * Verifies that the series belongs to the specified customer.
     * Supports partial updates for all series fields.
     * Can replace weekday slots if weekdays array is provided.
     * 
     * Query parameters:
     * - regenerate: whether to regenerate future occurrences (default: false)
     * - weeks: number of weeks to generate if regenerating (default: 12, max: 52)
     * 
     * If regenerate=true:
     * - Deletes all future appointments created from this series
     * - Generates new appointments based on updated configuration
     *
     * @param UpdateAppointmentSeriesRequest $request The validated series data
     * @param Customer $customer The customer who owns the series
     * @param AppointmentSeries $series The series to update
     * @return JsonResponse The updated series or conflict error (409)
     */
    public function update(UpdateAppointmentSeriesRequest $request, Customer $customer, AppointmentSeries $series): JsonResponse
    {
        $this->assertBelongsToCustomer($customer, $series);

        $data = $request->validated();

        // normalize timezone if omitted (keep existing)
        if (array_key_exists('timezone', $data) && ($data['timezone'] === null || $data['timezone'] === '')) {
            $data['timezone'] = config('app.timezone');
        }

        $weekdaysProvided = array_key_exists('weekdays', $data);
        $weekdays = $data['weekdays'] ?? null;
        unset($data['weekdays']);

        $regenerate = $request->boolean('regenerate', false);
        $weeks = max(1, min((int) $request->query('weeks', 12), 52));

        try {
            $updated = DB::transaction(function () use ($series, $data, $weekdaysProvided, $weekdays, $regenerate, $weeks) {
                if (!empty($data)) {
                    $series->update($data);
                }

                if ($weekdaysProvided) {
                    // Estratégia simples: substitui todos os slots
                    $series->weekdays()->delete();
                    $series->weekdays()->createMany($weekdays ?? []);
                }

                if ($regenerate) {
                    $this->deleteFutureOccurrences($series);
                    $this->generateOccurrences($series, $weeks);
                }

                return $series->load('weekdays');
            });
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'appointments_no_overlap')) {
                return response()->json([
                    'message' => 'Conflito de horário: a atualização gerou ocorrências que colidem com marcações existentes.',
                ], 409);
            }
            throw $e;
        }

        return response()->json(new AppointmentSeriesResource($updated));
    }

    /**
     * Deactivate an appointment series.
     * 
     * Sets is_active=false instead of deleting the series record.
     * Verifies that the series belongs to the specified customer.
     * 
     * Query parameters:
     * - delete_future: whether to delete future occurrences (default: false)
     * 
     * If delete_future=true, soft deletes all future appointments created from this series.
     *
     * @param Request $request The HTTP request with optional delete_future parameter
     * @param Customer $customer The customer who owns the series
     * @param AppointmentSeries $series The series to deactivate
     * @return JsonResponse Empty response with 204 status
     */
    public function destroy(Request $request, Customer $customer, AppointmentSeries $series): JsonResponse
    {
        $this->assertBelongsToCustomer($customer, $series);

        $deleteFuture = $request->boolean('delete_future', false);

        DB::transaction(function () use ($series, $deleteFuture) {
            $series->update(['is_active' => false]);

            if ($deleteFuture) {
                $this->deleteFutureOccurrences($series);
            }
        });

        return response()->json(null, 204);
    }

    private function assertBelongsToCustomer(Customer $customer, AppointmentSeries $series): void
    {
        if ($series->customer_id !== $customer->id) {
            abort(404);
        }
    }

    /**
     * Creates individual appointment records for the next N weeks based on:
     * - Series start/end dates
     * - Weekday time slots (0=Sunday through 6=Saturday)
     * - Series timezone for accurate datetime calculations
     * 
     * Generation logic:
     * - Starts from the later of: series.starts_on or today (no historical generation)
     * - Ends at the earlier of: series.ends_on or (today + weeks)
     * - Iterates day-by-day, creating appointments for matching weekdays
     * - Stores times in UTC after calculating in series timezone
     * - Uses batch insert for efficiency
     * 
     * Skips generation if series is inactive.
     *
     * @param AppointmentSeries $series The series to generate occurrences from
     * @param int $weeks Number of weeks into the future to generate (1-52)
     * @return void
     */
    private function generateOccurrences(AppointmentSeries $series, int $weeks): void
    {
        if (!$series->is_active) {
            return;
        }

        $series->loadMissing('weekdays');

        $tz = $series->timezone ?: config('app.timezone');

        $startOn = CarbonImmutable::parse($series->starts_on, $tz)->startOfDay();

        // horizonte: hoje + N semanas, mas respeita ends_on se existir
        $horizonEnd = CarbonImmutable::now($tz)->addWeeks($weeks)->endOfDay();

        if ($series->ends_on) {
            $endsOn = CarbonImmutable::parse($series->ends_on, $tz)->endOfDay();
            if ($endsOn < $horizonEnd) {
                $horizonEnd = $endsOn;
            }
        }

        // começa no maior entre starts_on e hoje (pra não gerar histórico antigo)
        $cursor = $startOn;
        $today = CarbonImmutable::now($tz)->startOfDay();
        if ($today > $cursor) {
            $cursor = $today;
        }

        $toInsert = [];

        // percorrer dia a dia (simples e suficiente pra MVP)
        while ($cursor <= $horizonEnd) {
            $weekday = (int) $cursor->dayOfWeek; // 0=Sunday ... 6=Saturday

            foreach ($series->weekdays as $slot) {
                if ((int) $slot->weekday !== $weekday) {
                    continue;
                }

                // monta datetime no timezone da série
                $startsAt = CarbonImmutable::parse($cursor->toDateString() . ' ' . $slot->start_time, $tz);
                $endsAt = CarbonImmutable::parse($cursor->toDateString() . ' ' . $slot->end_time, $tz);

                // garante end > start (só por segurança)
                if ($endsAt <= $startsAt) {
                    continue;
                }

                $toInsert[] = [
                    'customer_id' => $series->customer_id,
                    'series_id' => $series->id,
                    'title' => $series->title,
                    'notes' => $series->notes,
                    'starts_at' => $startsAt->utc()->toDateTimeString(),
                    'ends_at' => $endsAt->utc()->toDateTimeString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $cursor = $cursor->addDay();
        }

        if (!empty($toInsert)) {
            // insert em lote
            Appointment::insert($toInsert);
        }
    }

    /**
     * Delete future appointment occurrences created from a series.
     * 
     * Soft deletes all appointments where:
     * - series_id matches the given series
     * - starts_at is in the future (>= now)
     * 
     * Used when regenerating occurrences or cleaning up after series deactivation.
     *
     * @param AppointmentSeries $series The series whose future occurrences to delete
     * @return void
     */
    private function deleteFutureOccurrences(AppointmentSeries $series): void
    {
        Appointment::query()
            ->where('series_id', $series->id)
            ->where('starts_at', '>=', now())
            ->delete();
    }
}
