<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'sometimes|string|max:255|email:rfc,dns',
            'bio' =>  'nullable|string',
            'social' =>  'nullable|string',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        //
    }


    public function updateBio(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'bio' => 'nullable|string|max:2000',
            'social' => 'nullable'
        ]);

        $employee->update($data);
        return back()->withSuccess('Profile has been updated successfullly!');
    }

    public function appointments()
    {
        $user = auth()->guard('web')->user();
        $employee = $user->employee ?? null;

        if (!$employee) {
            abort(404, 'Employee not found.');
        }

        $appointments = Appointment::where('employee_id', $employee->id)
            ->with(['service', 'user'])
            ->latest()
            ->get();
        return view('employee.appointments', compact('appointments'));
    }
}
