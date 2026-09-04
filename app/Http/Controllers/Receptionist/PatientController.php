<?php

declare(strict_types=1);

namespace App\Http\Controllers\Receptionist;

use App\Actions\Patient\RegisterPatientAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StorePatientRequest;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PatientController
 *
 * @package App\Http\Controllers\Receptionist
 */
class PatientController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $status = $this->authorize('viewAny', Patient::class);
        // trace_reach('PatientController@index - auth', $status);

        $searchTerm = $request->input('search');

        $patients = Patient::with('user')
            ->when($searchTerm, static function ($query, $search): void {
                $query->whereHas('user', static function ($q) use ($search): void {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('identity_number', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate((int) config('clinic.pagination.default', 15))
            ->withQueryString();

        return Inertia::render('Receptionist/Patients/Index', [
            'patients' => $patients,
            'filters'  => $request->only(['search'])
        ]);
    }

    public function create(): InertiaResponse
    {
        $this->authorize('create', Patient::class);

        return Inertia::render('Receptionist/Patients/Create');
    }

    public function store(
        StorePatientRequest $request,
        RegisterPatientAction $registerPatientAction
    ): RedirectResponse {
        $this->authorize('create', Patient::class);

        $staff = $request->user()->primaryRole()->name;
        $data = array_merge($request->validated(), ['staff' => $staff]);
        $registerPatientAction->execute($data);

        return redirect()
            ->route('receptionist.patients.index')
            ->with('success', 'Comprehensive medical profile and identity records structured perfectly.');
    }

    public function show(Patient $patient): InertiaResponse
    {
        $this->authorize('view', $patient);

        $patient->load([
            'user',
            'appointments' => static function ($query): void {
                $query->with(['doctor.user', 'invoices'])
                    ->orderBy('appointment_date', 'desc')
                    ->orderBy('start_time', 'desc');
            }
        ]);

        return Inertia::render('Receptionist/Patients/Show', [
            'patient' => $patient
        ]);
    }

    /**
     * Public utility check -- no model-level authorization needed (does not expose or mutate
     * any Patient/User record, only confirms whether a candidate username string is already
     * taken, consistent with RegisterEmailCheckController's equivalent email check).
     */
    public function checkUsername(Request $request): JsonResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:25']
        ]);

        $isAllocated = User::where('username', $request->input('username'))->exists();

        return new JsonResponse([
            'valid'   => !$isAllocated,
            'message' => $isAllocated ? 'System identifier already claimed.' : 'Identifier available for assignment.'
        ], Response::HTTP_OK);
    }
}
