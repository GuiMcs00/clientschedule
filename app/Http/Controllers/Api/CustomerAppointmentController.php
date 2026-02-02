<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Customer;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller for managing customer appointments.
 * 
 * Handles CRUD operations for appointments scoped to a specific customer.
 * Includes conflict detection using PostgreSQL exclusion constraints to prevent overlapping appointments.
 * Supports filtering by date range and status (active, trashed, all).
 */
class CustomerAppointmentController extends Controller
{
    /**
     * List all appointments for a specific customer.
     * 
     * Supports filtering by:
     * - Date range (from/to query parameters)
     * - Status: active (default), trashed, or all
     * - Optional series inclusion via include_series parameter
     *
     * @param Request $request The HTTP request with optional filters
     * @param Customer $customer The customer whose appointments to retrieve
     * @return JsonResponse Paginated collection of appointments
     */
    public function index(Request $request, Customer $customer): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 15), 100);

        $query = Appointment::query()
            ->where('customer_id', $customer->id)
            ->orderBy('starts_at');

        // filtros opcionais (muito útil pro painel)
        if ($request->filled('from')) {
            $query->where('starts_at', '>=', $request->query('from'));
        }
        if ($request->filled('to')) {
            $query->where('starts_at', '<=', $request->query('to'));
        }

        // status: active|trashed|all
        $status = $request->query('status', 'active');
        if ($status === 'trashed') {
            $query->onlyTrashed();
        } elseif ($status === 'all') {
            $query->withTrashed();
        }

        // se quiser incluir série no retorno (opcional)
        if ($request->boolean('include_series')) {
            $query->with(['series.weekdays']);
        }

        $appointments = $query->paginate($perPage);

        return response()->json(AppointmentResource::collection($appointments));
    }

    /**
     * Create a new appointment for a customer.
     * 
     * Automatically sets customer_id from the route parameter for security.
     * Catches PostgreSQL exclusion constraint violations to detect time conflicts.
     *
     * @param StoreAppointmentRequest $request The validated appointment data
     * @param Customer $customer The customer for whom to create the appointment
     * @return JsonResponse The created appointment or conflict error (409)
     */
    public function store(StoreAppointmentRequest $request, Customer $customer): JsonResponse
    {
        $data = $request->validated();

        // garante ownership pelo path (não confia no body)
        $data['customer_id'] = $customer->id;

        try {
            $appointment = Appointment::create($data);
        } catch (QueryException $e) {
            // Anti-overlap no Postgres (exclusion constraint)
            // SQLSTATE 23xxx = constraint violation. A mais comum é 23505/23P01.
            // Não dá pra garantir o código exato sem ver, então tratamos por mensagem/constraint name.
            if (str_contains($e->getMessage(), 'appointments_no_overlap')) {

                return response()->json([
                    'message' => 'Conflito de horário: já existe uma marcação nesse intervalo.',
                ], 409);
            }
            throw $e;
        }

        return response()->json(new AppointmentResource($appointment), 201);
    }

    /**
     * Get a specific appointment for a customer.
     * 
     * Verifies that the appointment belongs to the specified customer.
     *
     * @param Customer $customer The customer who owns the appointment
     * @param Appointment $appointment The appointment to retrieve
     * @return JsonResponse The appointment details
     */
    public function show(Customer $customer, Appointment $appointment): JsonResponse
    {
        $this->assertBelongsToCustomer($customer, $appointment);

        // se quiser carregar série:
        // $appointment->loadMissing(['series.weekdays']);

        return response()->json(new AppointmentResource($appointment));
    }

    /**
     * Update a customer's appointment.
     * 
     * Verifies that the appointment belongs to the specified customer.
     * Catches PostgreSQL exclusion constraint violations to detect time conflicts.
     *
     * @param UpdateAppointmentRequest $request The validated appointment data
     * @param Customer $customer The customer who owns the appointment
     * @param Appointment $appointment The appointment to update
     * @return JsonResponse The updated appointment or conflict error (409)
     */
    public function update(UpdateAppointmentRequest $request, Customer $customer, Appointment $appointment): JsonResponse
    {
        $this->assertBelongsToCustomer($customer, $appointment);

        $data = $request->validated();

        try {
            $appointment->update($data);
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'appointments_no_overlap')) {
                return response()->json([
                    'message' => 'Conflito de horário: já existe uma marcação nesse intervalo.',
                ], 409);
            }
            throw $e;
        }

        return response()->json(new AppointmentResource($appointment));
    }

    /**
     * Soft delete a customer's appointment.
     * 
     * Verifies that the appointment belongs to the specified customer.
     *
     * @param Customer $customer The customer who owns the appointment
     * @param Appointment $appointment The appointment to delete
     * @return JsonResponse Empty response with 204 status
     */
    /**
     * Assert that an appointment belongs to the specified customer.
     * 
     * Returns 404 if the appointment doesn't belong to the customer,
     * preventing information leakage about appointments of other customers.
     *
     * @param Customer $customer The expected owner of the appointment
     * @param Appointment $appointment The appointment to verify
     * @return void
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException If ownership check fails
     */
    public function destroy(Customer $customer, Appointment $appointment): JsonResponse
    {
        $this->assertBelongsToCustomer($customer, $appointment);

        $appointment->delete();

        return response()->json(null, 204);
    }

    private function assertBelongsToCustomer(Customer $customer, Appointment $appointment): void
    {
        if ($appointment->customer_id !== $customer->id) {
            abort(404); // evita vazar que existe appointment de outro customer
        }
    }
}
