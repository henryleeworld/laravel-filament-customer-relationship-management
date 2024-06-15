<?php

namespace Database\Seeders;

use App\Models\CustomField;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomFieldSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        $customFields = [
            __('Birth Date'),
            __('Company'),
            __('Job Title'),
            __('Family Members'),
        ];

        foreach ($customFields as $customField) {
            CustomField::create(['name' => $customField]);
        }
    }
}
