<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Pricing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class PricingController extends Controller
{
    /**
     * Display a listing of the doctor's services.
     */
    public function index(Request $request): Response
    {
        $doctor = $request->user()->doctor;

        $pricings = Pricing::where('doctor_id', $doctor->id)
            ->when($request->search, function ($query, $search) {
                $query->where('service_name', 'like', "%{$search}%");
            })
            ->orderBy('service_name', 'asc')
            ->get();

        return inertia('Doctor/Pricings/Index', [
            'pricings' => $pricings,
            'filters'  => $request->only(['search']),
        ]);
    }

    /**
     * Store a newly created service in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $doctor = $request->user()->doctor;

        $input = $request->all();
        Validator::make($input, [
            'service_name' => 'required|string|max:150',
            'amount'       => 'required|numeric|min:0|max:999999.99',
        ])->validate();

        Pricing::create([
            'doctor_id'    => $doctor->id,
            'service_name' => $input['service_name'],
            'amount'       => $input['amount'],
        ]);

        return redirect()->back()->with('success', 'Service added successfully!');
    }

    /**
     * Update the specified service in storage.
     */
    public function update(Request $request, Pricing $pricing): RedirectResponse
    {
        if ($pricing->doctor_id !== $request->user()->doctor->id) {
            abort(403, 'Unauthorized action.');
        }

        $input = $request->all();
        Validator::make($input, [
            'service_name' => 'required|string|max:150',
            'amount'       => 'required|numeric|min:0|max:999999.99',
        ])->validate();

        $pricing->update([
            'service_name' => $input['service_name'],
            'amount'       => $input['amount'],
        ]);

        return redirect()->back()->with('success', 'Service updated successfully!');
    }

    /**
     * Remove the specified service from storage.
     */
    public function destroy(Request $request, Pricing $pricing): RedirectResponse
    {
        if ($pricing->doctor_id !== $request->user()->doctor->id) {
            abort(403, 'Unauthorized action.');
        }

        $pricing->delete();

        return redirect()->back()->with('success', 'Service deleted successfully!');
    }
}
