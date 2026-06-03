<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Perfect for small schools getting started with digital management.',
                'price' => 15000.00,
                'billing_cycle' => 'monthly',
                'features' => [
                    'Student Management',
                    'Basic Attendance Tracking',
                    'Fee Management',
                    'Basic Reports',
                    'Email Support',
                ],
                'max_students' => 100,
                'max_teachers' => 10,
                'max_staff' => 5,
                'has_hostel_management' => false,
                'has_accountant_management' => false,
                'has_exam_management' => false,
                'has_assignment_management' => false,
                'has_advanced_reports' => false,
                'has_api_access' => false,
                'has_custom_branding' => false,
                'has_priority_support' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'Comprehensive solution for growing institutions with advanced features.',
                'price' => 35000.00,
                'billing_cycle' => 'monthly',
                'features' => [
                    'Everything in Starter',
                    'Exam & Results Management',
                    'Assignment Management',
                    'Advanced Attendance',
                    'Hostel Management',
                    'Advanced Reports',
                    'Priority Support',
                ],
                'max_students' => 500,
                'max_teachers' => 50,
                'max_staff' => 25,
                'has_hostel_management' => true,
                'has_accountant_management' => true,
                'has_exam_management' => true,
                'has_assignment_management' => true,
                'has_advanced_reports' => true,
                'has_api_access' => false,
                'has_custom_branding' => true,
                'has_priority_support' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Complete solution for large institutions with unlimited access.',
                'price' => 75000.00,
                'billing_cycle' => 'monthly',
                'features' => [
                    'Everything in Professional',
                    'Unlimited Students & Staff',
                    'API Access',
                    'Custom Branding',
                    'Multi-Campus Support',
                    'Dedicated Support',
                    'Custom Integrations',
                ],
                'max_students' => null, // unlimited
                'max_teachers' => null, // unlimited
                'max_staff' => null, // unlimited
                'has_hostel_management' => true,
                'has_accountant_management' => true,
                'has_exam_management' => true,
                'has_assignment_management' => true,
                'has_advanced_reports' => true,
                'has_api_access' => true,
                'has_custom_branding' => true,
                'has_priority_support' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $planData) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );
        }
    }
}
