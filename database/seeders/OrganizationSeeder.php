<?php

namespace Database\Seeders;

use App\Models\Hierarchy;
use App\Models\Squad;
use App\Models\SubCompany;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $pathGroup = SubCompany::query()->create([
            'name' => 'Path Group',
            'description' => 'Primary business unit',
        ]);

        $marnGroup = SubCompany::query()->create([
            'name' => 'Marn Group',
            'description' => 'Secondary unit',
        ]);

        $digitalFactory = SubCompany::query()->create([
            'name' => 'Digital Factory',
            'description' => 'Technology unit',
        ]);

        $companyLevels = [
            ['level' => 1, 'title' => 'Group CEO', 'scope' => 'Executive leadership', 'type' => 'company'],
            ['level' => 2, 'title' => 'CTO', 'scope' => 'Tech oversight', 'type' => 'company'],
        ];

        $squadLevels = [
            ['level' => 1, 'title' => 'Squad CEO', 'scope' => 'Strategic lead', 'type' => 'squad'],
            ['level' => 2, 'title' => 'Squad Owner', 'scope' => 'Operational accountability', 'type' => 'squad'],
            ['level' => 3, 'title' => 'Squad Team Lead', 'scope' => 'Day-to-day delivery', 'type' => 'squad'],
            ['level' => 4, 'title' => 'Squad Member - Senior', 'scope' => 'Experienced contributor', 'type' => 'squad'],
            ['level' => 5, 'title' => 'Squad Member - Junior', 'scope' => 'Entry-level contributor', 'type' => 'squad'],
        ];

        foreach (array_merge($companyLevels, $squadLevels) as $level) {
            Hierarchy::query()->create($level);
        }

        Squad::query()->create(['sub_company_id' => $pathGroup->id, 'name' => 'Geek Squad']);
        Squad::query()->create(['sub_company_id' => $marnGroup->id, 'name' => 'University Squad']);
        Squad::query()->create(['sub_company_id' => $digitalFactory->id, 'name' => 'Product Squad']);
    }
}
