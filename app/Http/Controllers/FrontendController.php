<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Appointment;
use Spatie\OpeningHours\OpeningHours;
use Carbon\Carbon;
use Illuminate\Support\Number;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;

class FrontendController extends Controller
{

    public function __construct()
    {
        $setting = Setting::firstOrFail();
        View::share('setting', $setting);
    }

    public function index()
    {
        $categories = Category::with([
            'services' => function ($query) {
                $query->where('status', 1) // Only active services
                    ->with(['employees' => function ($query) {
                        $query->whereHas('user', function ($q) {
                            $q->whereDoesntHave('roles', function ($roleQuery) {
                                $roleQuery->where('name', 'admin');
                            });
                        });
                    }]);
            }
        ])->where('status', 1)->get();

        $employees = Employee::whereHas('user', function ($query) {
            $query->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'admin');
            });
        })->with('services')->with('user')->get();

        return view('frontend.index', compact('categories', 'employees'));
    }


    public function getServices(Request $request, Category $category)
    {
        $setting = Setting::firstOrFail();

        $services = $category->services()
            ->where('status', 1)
            ->with('category')
            ->get()
            ->map(function ($service) use ($setting) {
                if (isset($service->price)) {
                    $service->price = Number::currency($service->price, $setting->currency);
                }

                if (isset($service->sale_price)) {
                    $service->sale_price = Number::currency($service->sale_price, $setting->currency);
                }

                return $service;
            });

        return response()->json([
            'success' => true,
            'services' => $services
        ]);
    }


    public function getEmployees(Request $request, Service $service)
    {
        // Log the incoming service for debugging
        Log::info('Getting employees for service:', ['service_id' => $service->id, 'service_name' => $service->name]);

        $employees = Employee::where('category_id', $service->category_id)
            ->whereHas('user', function ($query) {
                $query->where('status', 1)
                    ->whereDoesntHave('roles', function ($q) {
                        $q->where('name', 'admin');
                    });
            })
            ->with(['user', 'services']) // Eager load user and services
            ->get();

        // Log the query results for debugging
        Log::info('Query results:', [
            'employee_count' => $employees->count(),
            'employees' => $employees->map(function ($emp) {
                return [
                    'id' => $emp->id,
                    'name' => $emp->user->name,
                    'category' => $emp->category->title,
                    'service_count' => $emp->services->count()
                ];
            })->toArray()
        ]);

        if ($employees->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No employees available for this service'
            ]);
        }

        return response()->json([
            'success' => true,
            'employees' => $employees,
            'service' => $service
        ]);
    }




    public function getEmployeeAvailability(Employee $employee, $date = null)
    {
        try {
            // Ensure the date is a Carbon instance, default to today if not provided
            $date = $date ? Carbon::parse($date) : Carbon::today();

            // Log the incoming request details for debugging
            Log::info('getEmployeeAvailability: Request received', [
                'employee_id' => $employee->id,
                'date' => $date->toDateString(),
                'raw_date_input' => $date
            ]);

            // Validate employee data
            if (!$employee->slot_duration || !$employee->break_duration) {
                Log::error('getEmployeeAvailability: Employee configuration missing', [
                    'employee_id' => $employee->id,
                    'slot_duration' => $employee->slot_duration,
                    'break_duration' => $employee->break_duration
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Employee configuration is incomplete. Please contact administrator.'
                ]);
            }

            // Get employee's working days from the database
            Log::debug('getEmployeeAvailability: Raw employee days string', ['raw_days' => $employee->days]);
            $employeeDays = json_decode($employee->days, true);
            Log::debug('getEmployeeAvailability: Decoded employee days', ['employee_days' => $employeeDays]);

            if (empty($employeeDays)) {
                Log::warning('getEmployeeAvailability: Employee working days are not defined.', ['employee_id' => $employee->id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Employee working schedule is not defined.'
                ]);
            }

            // Define opening hours using Spatie/OpeningHours
            $openingHoursData = [];
            foreach ($employeeDays as $dayName => $ranges) {
                $openingHoursData[$dayName] = $ranges;
            }

            // Get holidays
            $holidays = \App\Models\Holiday::all()->pluck('date')->map(function ($date) {
                return Carbon::parse($date)->toDateString();
            })->toArray();

            $formattedHolidays = [];
            foreach ($holidays as $holidayDate) {
                $formattedHolidays[$holidayDate] = []; // Empty array means closed all day
            }
            Log::debug('getEmployeeAvailability: Formatted holidays', ['formatted_holidays' => $formattedHolidays]);

            // Create OpeningHours instance with regular schedule and exceptions
            Log::debug('getEmployeeAvailability: Data being passed to OpeningHours::create', ['opening_hours_data' => array_merge($openingHoursData, ['exceptions' => $formattedHolidays])]);

            try {
                $openingHours = OpeningHours::create(array_merge($openingHoursData, ['exceptions' => $formattedHolidays]));
            } catch (\Exception $e) {
                Log::error('getEmployeeAvailability: Error creating OpeningHours instance', [
                    'error' => $e->getMessage(),
                    'opening_hours_data' => $openingHoursData
                ]);
                throw $e;
            }

            // Check if employee is open on the selected date
            if (!$openingHours->isOpenOn($date->toDateString())) {
                Log::info('getEmployeeAvailability: Employee is not available on this date due to schedule or holiday.', [
                    'employee_id' => $employee->id,
                    'date' => $date->toDateString(),
                    'is_holiday' => in_array($date->toDateString(), $holidays)
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Employee is not available on this date.'
                ]);
            }

            try {
                $openingHoursForDay = $openingHours->forDate($date);
            } catch (\Exception $e) {
                Log::error('getEmployeeAvailability: Error getting opening hours for date', [
                    'error' => $e->getMessage(),
                    'date' => $date->toDateString()
                ]);
                throw $e;
            }

            $availableRanges = [];
            foreach ($openingHoursForDay as $timeRange) {
                $availableRanges[] = (object)[
                    'start' => $timeRange->start()->format('H:i'),
                    'end' => $timeRange->end()->format('H:i'),
                ];
            }

            // Log generated available ranges
            Log::info('getEmployeeAvailability: Available ranges generated', ['ranges' => $availableRanges]);

            // Generate time slots using the helper method
            $slots = $this->generateTimeSlots(
                $availableRanges,
                $employee->slot_duration,
                $employee->break_duration,
                $date,
                $employee->id
            );

            // Log generated slots
            Log::info('getEmployeeAvailability: Generated time slots', ['slots_count' => count($slots), 'slots' => $slots]);

            if (empty($slots)) {
                Log::info('getEmployeeAvailability: No available time slots for this date after filtering.', [
                    'employee_id' => $employee->id,
                    'date' => $date->toDateString(),
                    'available_ranges' => $availableRanges
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'No available time slots for this date.'
                ]);
            }

            // Ensure we have valid data before sending response
            if (!is_array($slots)) {
                Log::error('getEmployeeAvailability: Invalid slots data type', [
                    'slots_type' => gettype($slots),
                    'slots' => $slots
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Error processing time slots.'
                ]);
            }

            return response()->json([
                'success' => true,
                'available_slots' => $slots,
                'slot_duration' => $employee->slot_duration,
                'break_duration' => $employee->break_duration,
                'date' => $date->toDateString(),
                'employee_id' => $employee->id
            ]);
        } catch (\Exception $e) {
            Log::error('getEmployeeAvailability: An error occurred', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'employee_id' => $employee->id ?? null,
                'date' => $date->toDateString() ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error loading availability: ' . $e->getMessage(),
                'debug_info' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => env('APP_DEBUG') ? $e->getTraceAsString() : 'Trace hidden in production'
                ]
            ], 500);
        }
    }


    protected function generateTimeSlots($availableRanges, $slotDuration, $breakDuration, $date, $employeeId)
    {
        $slots = [];
        $now = now();
        $isToday = $date->isToday();

        // Get existing appointments for this date and employee
        $existingAppointments = Appointment::where('booking_date', $date->toDateString())
            ->where('employee_id', $employeeId)
            ->whereNotIn('status', ['Cancelled']) // Exclude cancelled/ here could add more status to make expection
            ->get(['booking_time']);

        // Convert existing appointments to time ranges we can compare against
        $bookedSlots = $existingAppointments->map(function ($appointment) {
            $times = explode(' - ', $appointment->booking_time);
            return [
                'start' => Carbon::createFromFormat('g:i A', trim($times[0]))->format('H:i'),
                'end' => Carbon::createFromFormat('g:i A', trim($times[1]))->format('H:i')
            ];
        })->toArray();

        foreach ($availableRanges as $range) {
            $start = Carbon::parse($date->toDateString() . ' ' . $range->start);
            $end = Carbon::parse($date->toDateString() . ' ' . $range->end);

            // Skip if the entire range is in the past (only for today)
            if ($isToday && $end->lte($now)) {
                continue;
            }

            $currentSlotStart = clone $start;

            // If today and current slot start is in the past, adjust to current time
            if ($isToday && $currentSlotStart->lt($now)) {
                $currentSlotStart = clone $now;

                // Round up to nearest slot interval
                $minutes = $currentSlotStart->minute;
                $remainder = $minutes % $slotDuration;
                if ($remainder > 0) {
                    $currentSlotStart->addMinutes($slotDuration - $remainder)->second(0);
                }
            }

            while ($currentSlotStart->copy()->addMinutes($slotDuration)->lte($end)) {
                $slotEnd = $currentSlotStart->copy()->addMinutes($slotDuration);

                // Check if this slot conflicts with any existing booking
                $isAvailable = true;
                foreach ($bookedSlots as $bookedSlot) {
                    $bookedStart = Carbon::parse($date->toDateString() . ' ' . $bookedSlot['start']);
                    $bookedEnd = Carbon::parse($date->toDateString() . ' ' . $bookedSlot['end']);

                    if ($currentSlotStart->lt($bookedEnd) && $slotEnd->gt($bookedStart)) {
                        $isAvailable = false;
                        break;
                    }
                }

                // Only add slots that are available and in the future (for today)
                if ($isAvailable && (!$isToday || $slotEnd->gt($now))) {
                    $slots[] = [
                        'start' => $currentSlotStart->format('H:i'),
                        'end' => $slotEnd->format('H:i'),
                        'display' => $currentSlotStart->format('g:i A') . ' - ' . $slotEnd->format('g:i A'),
                    ];
                }

                // Add break duration if specified
                $currentSlotStart->addMinutes($slotDuration + $breakDuration);

                // Check if next slot would exceed end time
                if ($currentSlotStart->copy()->addMinutes($slotDuration)->gt($end)) {
                    break;
                }
            }
        }

        return $slots;
    }
}
