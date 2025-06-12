<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Setting;
use App\Models\Service;
use Carbon\Carbon;
use App\Events\StatusUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $recentActivity = null;
        $serviceStats = null;
        $appointmentStats = null;
        $appointments = null;

        if ($user->hasRole('admin')) {
            // User counts
            $adminCount = \App\Models\User::role('admin')->count();
            $employeeCount = \App\Models\User::role('employee')->count();
            $clientCount = \App\Models\User::role('subscriber')->count();

            // Get last 7 days of appointment statistics
            $appointmentStats = Appointment::select(
                DB::raw('DATE(booking_date) as date'),
                DB::raw('COUNT(*) as total'),
                'status'
            )
                ->where('booking_date', '>=', Carbon::now()->subDays(7))
                ->groupBy('date', 'status')
                ->get()
                ->groupBy('date');

            // Get service popularity statistics
            $serviceStats = Service::withCount('appointments')
                ->orderBy('appointments_count', 'desc')
                ->take(5)
                ->get();

            // Get recent activity
            $recentActivity = [
                'appointments' => Appointment::with(['service', 'employee.user'])
                    ->latest()
                    ->take(5)
                    ->get(),
                'newUsers' => \App\Models\User::latest()
                    ->take(5)
                    ->get(),
                'statusChanges' => Appointment::whereNotNull('updated_at')
                    ->where('updated_at', '>=', Carbon::now()->subDays(7))
                    ->with(['service', 'employee.user'])
                    ->latest('updated_at')
                    ->take(5)
                    ->get()
            ];

            // Prepare chart data for appointment trends
            $chartData = [
                'labels' => $appointmentStats->keys()->map(function ($date) {
                    return Carbon::parse($date)->format('M d');
                })->toArray(),
                'datasets' => [
                    [
                        'label' => 'Confirmed',
                        'data' => $this->getStatusCounts($appointmentStats, 'Confirmed'),
                        'backgroundColor' => 'rgba(46, 204, 113, 0.2)',
                        'borderColor' => 'rgba(46, 204, 113, 1)',
                    ],
                    [
                        'label' => 'Pending',
                        'data' => $this->getStatusCounts($appointmentStats, 'Pending payment'),
                        'backgroundColor' => 'rgba(243, 156, 18, 0.2)',
                        'borderColor' => 'rgba(243, 156, 18, 1)',
                    ],
                    [
                        'label' => 'Cancelled',
                        'data' => $this->getStatusCounts($appointmentStats, 'Cancelled'),
                        'backgroundColor' => 'rgba(231, 76, 60, 0.2)',
                        'borderColor' => 'rgba(231, 76, 60, 1)',
                    ]
                ]
            ];
        } else {
            // For non-admin users, fetch their appointments for the calendar
            $query = Appointment::with(['service', 'employee.user']);

            if ($user->hasRole('employee')) {
                // If user is an employee, show appointments assigned to them
                $query->where('employee_id', $user->employee->id);
            } else {
                // If user is a subscriber, show their own appointments
                $query->where('user_id', $user->id);
            }

            $appointments = $query->get()->map(function ($appointment) {
                Log::debug('DashboardController: Processing appointment booking_time', ['booking_time' => $appointment->booking_time, 'appointment_id' => $appointment->id]);

                // Parse the booking time to get start and end times flexibly
                $times = explode(' - ', $appointment->booking_time);

                if (count($times) === 2) {
                    $start_time_str = $times[0];
                    $end_time_str = $times[1];
                } else {
                    // Fallback for old or malformed data: assume booking_time is just the start time
                    Log::warning('DashboardController: Unexpected booking_time format', [
                        'booking_time' => $appointment->booking_time,
                        'appointment_id' => $appointment->id
                    ]);
                    $start_time_str = $appointment->booking_time; // Use the whole string as start time
                    $end_time_str = $appointment->booking_time;   // Use the whole string as end time
                }

                Log::debug('DashboardController: Extracted time strings', [
                    'start_time_str' => $start_time_str,
                    'end_time_str' => $end_time_str
                ]);

                // Use Carbon::parse for more flexible parsing of time strings
                $startTime = Carbon::parse(trim($start_time_str));
                $endTime = Carbon::parse(trim($end_time_str));

                return [
                    'id' => $appointment->id,
                    'title' => $appointment->name . ' - ' . $appointment->service->title,
                    'start' => $appointment->booking_date . 'T' . $startTime->format('H:i:s'),
                    'end' => $appointment->booking_date . 'T' . $endTime->format('H:i:s'),
                    'color' => $this->getStatusColor($appointment->status),
                    'status' => $appointment->status,
                    'extendedProps' => [
                        'service' => $appointment->service->title,
                        'employee' => $appointment->employee->user->name,
                        'notes' => $appointment->notes,
                        'email' => $appointment->email,
                        'phone' => $appointment->phone,
                        'amount' => $appointment->amount
                    ]
                ];
            });
        }

        return view('backend.dashboard.index', compact(
            'adminCount',
            'employeeCount',
            'clientCount',
            'chartData',
            'recentActivity',
            'serviceStats',
            'appointments'
        ));
    }

    private function getStatusCounts($stats, $status)
    {
        return $stats->map(function ($day) use ($status) {
            return $day->where('status', $status)->sum('total');
        })->toArray();
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
