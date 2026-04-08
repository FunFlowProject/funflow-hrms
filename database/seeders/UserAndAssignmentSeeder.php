<?php

namespace Database\Seeders;

use App\Models\EmployeeAssignment;
use App\Models\Hierarchy;
use App\Models\Squad;
use App\Models\SubCompany;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserAndAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $pathGroup = SubCompany::query()->where('name', 'Path Group')->firstOrFail();
        $marnGroup = SubCompany::query()->where('name', 'Marn Group')->firstOrFail();

        $geekSquad = Squad::query()->where('name', 'Geek Squad')->firstOrFail();
        $universitySquad = Squad::query()->where('name', 'University Squad')->firstOrFail();

        $groupCeo = Hierarchy::query()->where('title', 'Group CEO')->firstOrFail();
        $squadOwner = Hierarchy::query()->where('title', 'Squad Owner')->firstOrFail();
        $seniorMember = Hierarchy::query()->where('title', 'Squad Member - Senior')->firstOrFail();
        $juniorMember = Hierarchy::query()->where('title', 'Squad Member - Junior')->firstOrFail();

        $executive = User::query()->create([
            'full_name' => 'Executive User',
            'email' => 'executive@example.com',
            'username' => 'executive.user',
            'phone_number' => '+10000000001',
            'date_of_birth' => '1985-05-10',
            'hire_date' => '2020-01-01',
            'contract_type' => 'full-time',
            'system_role' => 'admin',
            'status' => 'joined',
            'password' => 'Password@123',
            'email_verified_at' => now(),
        ]);

        $hr = User::query()->create([
            'full_name' => 'HR User',
            'email' => 'hr@example.com',
            'username' => 'hr.user',
            'phone_number' => '+10000000002',
            'date_of_birth' => '1990-08-14',
            'hire_date' => '2021-03-15',
            'contract_type' => 'full-time',
            'system_role' => 'hr',
            'status' => 'joined',
            'password' => 'Password@123',
            'email_verified_at' => now(),
        ]);

        $employee = User::query()->create([
            'full_name' => 'Multi Assignment Employee',
            'email' => 'employee@example.com',
            'username' => 'multi.employee',
            'phone_number' => '+10000000003',
            'date_of_birth' => '1998-11-20',
            'hire_date' => '2023-06-01',
            'contract_type' => 'intern',
            'system_role' => 'employee',
            'status' => 'pending',
            'password' => 'Password@123',
            'email_verified_at' => now(),
        ]);

        $employeeRequester = User::query()->create([
            'full_name' => 'Employee Requester',
            'email' => 'requester@example.com',
            'username' => 'employee.requester',
            'phone_number' => '+10000000004',
            'date_of_birth' => '1996-07-18',
            'hire_date' => '2024-01-20',
            'contract_type' => 'full-time',
            'system_role' => 'employee',
            'status' => 'joined',
            'password' => 'Password@123',
            'email_verified_at' => now(),
        ]);

        $hrHandler = User::query()->create([
            'full_name' => 'HR Handler',
            'email' => 'handler@example.com',
            'username' => 'hr.handler',
            'phone_number' => '+10000000005',
            'date_of_birth' => '1992-04-11',
            'hire_date' => '2022-05-01',
            'contract_type' => 'full-time',
            'system_role' => 'hr',
            'status' => 'joined',
            'password' => 'Password@123',
            'email_verified_at' => now(),
        ]);

        EmployeeAssignment::query()->create([
            'user_id' => $executive->id,
            'sub_company_id' => $pathGroup->id,
            'squad_id' => null,
            'hierarchy_id' => $groupCeo->id,
        ]);

        EmployeeAssignment::query()->create([
            'user_id' => $hr->id,
            'sub_company_id' => $pathGroup->id,
            'squad_id' => $geekSquad->id,
            'hierarchy_id' => $squadOwner->id,
        ]);

        EmployeeAssignment::query()->create([
            'user_id' => $employee->id,
            'sub_company_id' => $pathGroup->id,
            'squad_id' => $geekSquad->id,
            'hierarchy_id' => $seniorMember->id,
        ]);

        EmployeeAssignment::query()->create([
            'user_id' => $employee->id,
            'sub_company_id' => $marnGroup->id,
            'squad_id' => $universitySquad->id,
            'hierarchy_id' => $juniorMember->id,
        ]);

        EmployeeAssignment::query()->create([
            'user_id' => $employeeRequester->id,
            'sub_company_id' => $marnGroup->id,
            'squad_id' => $universitySquad->id,
            'hierarchy_id' => $juniorMember->id,
        ]);

        EmployeeAssignment::query()->create([
            'user_id' => $hrHandler->id,
            'sub_company_id' => $pathGroup->id,
            'squad_id' => $geekSquad->id,
            'hierarchy_id' => $squadOwner->id,
        ]);
    }
}
