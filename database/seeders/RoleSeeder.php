<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // gets all permissions via Gate::before rule; see AuthServiceProvider

        $role_counts = Role::count();

        $crmRoles = [

            ['name' => 'Super Admin'],
            ['name' => 'Agent'],

            // Roles related to call center CRM
            ['name' => 'Call Center Manager'],
            ['name' => 'Call Center Agent'],
            ['name' => 'Call Quality Analyst'],
            ['name' => 'Customer Service Representative'],
            ['name' => 'IVR Specialist'],

            // Roles related to leads CRM
            ['name' => 'Lead Manager'],
            ['name' => 'Lead Agent'],
            ['name' => 'Lead Qualification Specialist'],
            ['name' => 'Lead Generation Specialist'],
            ['name' => 'Sales Development Representative'],

            // Roles related to sales CRM
            ['name' => 'Sales Manager'],
            ['name' => 'Sales Representative'],
            ['name' => 'Account Executive'],
            ['name' => 'Account Manager'],
            ['name' => 'Sales Operations Analyst'],
            ['name' => 'Sales Support Specialist'],

            // Additional roles for various functions
            ['name' => 'Support Specialist'],
            ['name' => 'Customer Success Manager'],
            ['name' => 'Marketing Specialist'],
            ['name' => 'Product Specialist'],
            ['name' => 'Technical Support'],
            ['name' => 'Data Analyst'],
            ['name' => 'Business Analyst'],
            ['name' => 'CRM Administrator'],
            ['name' => 'CRM Developer'],
            ['name' => 'Implementation Consultant'],

            // More roles...
            ['name' => 'Billing Specialist'],
            ['name' => 'Collections Specialist'],
            ['name' => 'Finance Manager'],
            ['name' => 'Operations Manager'],
            ['name' => 'Project Manager'],
            ['name' => 'Quality Assurance Manager'],
            ['name' => 'Training Specialist'],
            ['name' => 'Human Resources Coordinator'],
            ['name' => 'Recruiter'],
            ['name' => 'IT Support Specialist'],
            ['name' => 'Network Administrator'],
            ['name' => 'Database Administrator'],
            ['name' => 'Security Specialist'],
            ['name' => 'Software Engineer'],
            ['name' => 'UX/UI Designer'],
            ['name' => 'Content Writer'],
            ['name' => 'Social Media Specialist'],
            ['name' => 'SEO Specialist'],
            ['name' => 'E-commerce Specialist'],
            ['name' => 'Supply Chain Manager'],
            ['name' => 'Logistics Coordinator'],
            ['name' => 'Warehouse Manager'],
            ['name' => 'Inventory Control Specialist'],
            ['name' => 'Shipping and Receiving Clerk'],
            ['name' => 'Procurement Officer'],
        ];

        foreach ($crmRoles as $row) {
            Role::updateOrCreate(
                ['name' => $row['name']],
                [
                    'name' => $row['name'],
                    'guard_name' => 'web',
                    'user_id' => User::inRandomOrder()->first()->id,
                    // 80% 1
                    'status' => rand(0, 10) <= 8 ? 1 : 0,
                ]
            );
        }

        // Agent permissions

        $agent = Role::findByName('Agent');

        $agentPermissions = [
            'ticket_create',
            'ticket_edit',
            'ticket_show',
            'ticket_access',
        ];

        foreach ($agentPermissions as $permission) {
            $agent->givePermissionTo($permission);
        }


        if ($role_counts === 0) {

            try {
                // Delete the entire directory along with its contents
                Storage::deleteDirectory('system/roles');
                
                // Success message
                echo "The directory 'storage/app/system/roles/' and its contents have been deleted.\n";
            } catch (Exception $e) {
                // Handle any errors that may occur during the deletion process
                echo "An error occurred: " . $e->getMessage() . ".\n";
            }
        }
    }
}
