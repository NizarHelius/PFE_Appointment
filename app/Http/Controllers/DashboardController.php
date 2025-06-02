<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Setting;
use Carbon\Carbon;
use App\Events\StatusUpdated;

class DashboardController extends Controller
{
    public function index()
    {
        $setting = Setting::firstOrFail();
        $user = auth()->user();

        // Get user counts by role only for admin users
        $adminCount = null;
        $employeeCount = null;
        $clientCount = null;
        $chartData = null;

        if ($user->hasRole('admin')) {
            $adminCount = \App\Models\User::role('admin')->count();
            $employeeCount = \App\Models\User::role('employee')->count();
            $clientCount = \App\Models\User::role('subscriber')->count();

            // Get analytics data for the chart
            $chartData = [
                'labels' => ['Admins', 'Employees', 'Clients'],
                'data' => [$adminCount, $employeeCount, $clientCount],
                'backgroundColor' => [
                    'rgba(54, 162, 235, 0.2)',  // Blue for admins
                    'rgba(75, 192, 192, 0.2)',  // Green for employees
                    'rgba(255, 206, 86, 0.2)',  // Yellow for clients
                ],
                'borderColor' => [
                    'rgba(54, 162, 235, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 206, 86, 1)',
                ]
            ];
        }

        // Start with base query
        $query = Appointment::query()->with(['employee.user', 'service', 'user']);

        // Only admins can see all data - no conditions added
        if (!$user->hasRole('admin')) {
            $query->where(function ($q) use ($user) {
                if ($user->employee) {
                    $q->where('employee_id', $user->employee->id);
                }
                $q->orWhere('user_id', $user->id);
            });
        }

        // Format the appointments with proper date handling
        $appointments = $query->get()->map(function ($appointment) {
            try {
                if (!str_contains($appointment->booking_time ?? '', '-')) {
                    throw new \Exception("Invalid time format");
                }

                // Parse booking date
                $bookingDate = Carbon::parse($appointment->booking_date);

                // Parse start and end times
                [$startTime, $endTime] = array_map('trim', explode('-', $appointment->booking_time));

                // Create proper datetime objects
                $startDateTime = Carbon::createFromFormat('h:i A', $startTime)
                    ->setDate($bookingDate->year, $bookingDate->month, $bookingDate->day);

                $endDateTime = Carbon::createFromFormat('h:i A', $endTime)
                    ->setDate($bookingDate->year, $bookingDate->month, $bookingDate->day);

                // Handle overnight appointments (if end time is next day)
                if ($endDateTime->lt($startDateTime)) {
                    $endDateTime->addDay();
                }

                return [
                    'id' => $appointment->id, // Add appointment ID
                    'title' => sprintf(
                        '%s - %s',
                        $appointment->name,
                        $appointment->service->title ?? 'Service'
                    ),
                    'start' => $startDateTime->toIso8601String(),
                    'end' => $endDateTime->toIso8601String(),
                    'description' => $appointment->notes,
                    'email' => $appointment->email,
                    'phone' => $appointment->phone,
                    'amount' => $appointment->amount,
                    'status' => $appointment->status,
                    'staff' => $appointment->employee->user->name ?? 'Unassigned',
                    'color' => $this->getStatusColor($appointment->status),
                    'service_title' => $appointment->service->title ?? 'Service', // Add service title
                    'name' => $appointment->name, // Add client name
                    'notes' => $appointment->notes, // Add notes
                ];
            } catch (\Exception $e) {
                \Log::error("Format error for appointment {$appointment->id}: {$e->getMessage()}");
                return null;
            }
        })->filter();

        return view('backend.dashboard.index', compact('appointments', 'adminCount', 'employeeCount', 'clientCount', 'chartData'));
    }

    // Helper function to get color based on status
    private function getStatusColor($status)
    {
        $colors = [
            'Pending payment' => '#f39c12',
            'Processing' => '#3498db',
            'Confirmed' => '#2ecc71',
            'Cancelled' => '#ff0000',
            'Completed' => '#008000',
            'On Hold' => '#95a5a6',
            'Rescheduled' => '#f1c40f',
            'No Show' => '#e67e22',
        ];

        return $colors[$status] ?? '#7f8c8d';
    }


    // In AppointmentController.php
    public function updateStatus(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'status' => 'required|in:Pending payment,Processing,Confirmed,Cancelled,Completed,On Hold,No Show'
        ]);

        $appointment = Appointment::findOrFail($request->appointment_id);
        $appointment->status = $request->status;
        $appointment->save();

        event(new StatusUpdated($appointment));

        return back()->with('success', 'Status updated successfully');
    }
}
