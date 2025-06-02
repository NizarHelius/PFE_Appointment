<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Setting;
use App\Models\Employee;
use App\Models\Category;
use App\Models\Service;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        try {
            DB::beginTransaction();

            // Create settings if not exists
            if (Schema::hasTable('settings') && Setting::count() === 0) {
                Setting::create([
                    'bname' => 'Appointment System',
                    'email' => 'admin@example.com',
                    'phone' => '1234567890',
                    'whatsapp' => '1234567890',
                    'currency' => 'USD',
                    'address' => '123 Main Street',
                    'logo' => null,
                    'meta_title' => 'Appointment Booking System',
                    'meta_description' => 'A modern appointment booking system',
                    'meta_keywords' => 'appointment, booking, system',
                    'social' => json_encode([
                        'facebook' => 'https://facebook.com',
                        'twitter' => 'https://twitter.com',
                        'instagram' => 'https://instagram.com'
                    ]),
                    'smtp' => json_encode([
                        'host' => 'smtp.mailtrap.io',
                        'port' => '2525',
                        'username' => 'null',
                        'password' => 'null',
                        'encryption' => 'tls'
                    ]),
                    'other' => json_encode([
                        'timezone' => 'UTC',
                        'date_format' => 'Y-m-d',
                        'time_format' => 'H:i'
                    ]),
                    'map' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3022.2152561717667!2d-73.98784492426401!3d40.75790497138482!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c259a9b3117469%3A0xd134e199a405a163!2sEmpire%20State%20Building!5e0!3m2!1sen!2sus!4v1709331234567!5m2!1sen!2sus" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>',
                    'header' => null,
                    'footer' => null
                ]);
            }

            // Create permissions and roles first
            $this->createPermissionsAndRoles();

            // Create initial admin user if not exists
            if (Schema::hasTable('users') && User::count() === 0) {
                $user = $this->createInitialUserWithPermissions();
                $this->createCategoriesAndServices($user);
            }

            // Create dummy data
            $this->createDummyData();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Create permissions and roles
     */
    protected function createPermissionsAndRoles(): void
    {
        // Define permissions list
        $permissions = [
            'permissions.view',
            'permissions.create',
            'permissions.edit',
            'permissions.delete',
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'appointments.view',
            'appointments.create',
            'appointments.edit',
            'appointments.delete',
            'categories.view',
            'categories.create',
            'categories.edit',
            'categories.delete',
            'services.view',
            'services.create',
            'services.edit',
            'services.delete',
            'settings.edit'
        ];

        // Create each permission if it doesn't exist
        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        // Create roles if they do not exist
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee']);
        $subscriberRole = Role::firstOrCreate(['name' => 'subscriber']);

        // Assign all permissions to the 'admin' role
        $adminRole->syncPermissions(Permission::all());

        // Assign specific permissions to the 'employee' role
        $employeeRole = Role::findByName('employee');
        $appointmentsViewPermission = Permission::findByName('appointments.view');

        if ($employeeRole && $appointmentsViewPermission) {
            $employeeRole->givePermissionTo($appointmentsViewPermission);
        }
    }

    /**
     * Create the initial admin user
     */
    protected function createInitialUserWithPermissions(): User
    {
        // Create the initial admin user
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'phone' => '1234567890',
            'status' => true,
            'email_verified_at' => now(),
            'password' => Hash::make('admin123'),
        ]);

        // Assign the 'admin' role to the user
        $user->assignRole('admin');

        // Create admin as employee with additional details
        Employee::create([
            'user_id' => $user->id,
            'bio' => 'System Administrator',
            'days' => json_encode([
                "monday" => ["06:00-22:00"],
                "tuesday" => ["06:00-15:00", "16:00-22:00"],
                "wednesday" => ["09:00-12:00", "14:00-23:00"],
                "thursday" => ["09:00-20:00"],
                "friday" => ["06:00-17:00"],
                "saturday" => ["05:00-18:00"]
            ]),
            'slot_duration' => 30,
            'break_duration' => 15,
            'social' => json_encode([
                'facebook' => 'https://facebook.com',
                'twitter' => 'https://twitter.com'
            ]),
            'other' => json_encode([
                'specialization' => 'General Administration'
            ])
        ]);

        return $user;
    }

    /**
     * Create dummy data (employees and users)
     */
    protected function createDummyData(): void
    {
        // Create 7 employees
        for ($i = 1; $i <= 7; $i++) {
            $user = User::create([
                'name' => "Employee $i",
                'email' => "employee$i@example.com",
                'phone' => "06" . rand(10000000, 99999999),
                'status' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]);

            $user->assignRole('employee');

            Employee::create([
                'user_id' => $user->id,
                'bio' => "Professional Employee $i",
                'days' => json_encode([
                    "monday" => ["09:00-17:00"],
                    "tuesday" => ["09:00-17:00"],
                    "wednesday" => ["09:00-17:00"],
                    "thursday" => ["09:00-17:00"],
                    "friday" => ["09:00-17:00"]
                ]),
                'slot_duration' => 30,
                'break_duration' => 15,
                'social' => json_encode([
                    'facebook' => 'https://facebook.com',
                    'twitter' => 'https://twitter.com'
                ]),
                'other' => json_encode([
                    'specialization' => 'General Services'
                ])
            ]);
        }

        // Create 12 users (subscribers)
        for ($i = 1; $i <= 12; $i++) {
            $user = User::create([
                'name' => "User $i",
                'email' => "user$i@example.com",
                'phone' => "06" . rand(10000000, 99999999),
                'status' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]);

            $user->assignRole('subscriber');
        }
    }

    /**
     * Create categories and services
     */
    protected function createCategoriesAndServices(User $user): void
    {
        // Create categories
        $categories = [
            [
                'title' => 'Astrology',
                'slug' => 'astrology',
                'body' => 'Get insights into your future with our expert astrologers.'
            ],
            [
                'title' => 'Dentist',
                'slug' => 'dentist',
                'body' => 'Professional dental care for your perfect smile.'
            ],
            [
                'title' => 'Skin Specialist',
                'slug' => 'skin-specialist',
                'body' => 'Expert care for all your dermatological needs.'
            ]
        ];

        foreach ($categories as $categoryData) {
            $category = Category::create($categoryData);

            // Create 2 services for each category
            switch ($category->title) {
                case 'Astrology':
                    $services = [
                        [
                            'title' => 'Birth Chart Reading',
                            'slug' => 'birth-chart-reading',
                            'price' => 999,
                            'excerpt' => 'Detailed analysis of your natal chart for life insights.'
                        ],
                        [
                            'title' => 'Love Compatibility',
                            'slug' => 'love-compatibility',
                            'price' => 699,
                            'excerpt' => 'Understand your relationship dynamics through astrology.'
                        ]
                    ];
                    break;

                case 'Dentist':
                    $services = [
                        [
                            'title' => 'Teeth Cleaning',
                            'slug' => 'teeth-cleaning',
                            'price' => 750,
                            'excerpt' => 'Professional cleaning to maintain oral health.'
                        ],
                        [
                            'title' => 'Dental Implants',
                            'slug' => 'dental-implants',
                            'price' => 1500,
                            'excerpt' => 'Restore your smile with permanent tooth replacements.'
                        ]
                    ];
                    break;

                case 'Skin Specialist':
                    $services = [
                        [
                            'title' => 'Acne Treatment',
                            'slug' => 'acne-treatment',
                            'price' => 3500,
                            'excerpt' => 'Customized solutions for clear, healthy skin.'
                        ],
                        [
                            'title' => 'Anti-Aging Facial',
                            'slug' => 'anti-aging-facial',
                            'price' => 200,
                            'excerpt' => 'Rejuvenate your skin and reduce signs of aging.'
                        ]
                    ];
                    break;
            }

            foreach ($services as $serviceData) {
                Service::create([
                    'title' => $serviceData['title'],
                    'slug' => $serviceData['slug'],
                    'price' => $serviceData['price'],
                    'excerpt' => $serviceData['excerpt'],
                    'category_id' => $category->id
                ]);
            }
        }

        // Attach all services to the employee
        if ($user->employee) {
            $allServices = Service::all();
            $user->employee->services()->sync($allServices->pluck('id'));
        }
    }
}
