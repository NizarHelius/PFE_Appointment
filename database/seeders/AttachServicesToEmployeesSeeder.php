<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Service;

class AttachServicesToEmployeesSeeder extends Seeder
{
    public function run()
    {
        // Get all employees
        $employees = Employee::all();

        // Get all services
        $services = Service::all();

        // Attach all services to each employee
        foreach ($employees as $employee) {
            $employee->services()->sync($services->pluck('id'));
        }
    }
}
