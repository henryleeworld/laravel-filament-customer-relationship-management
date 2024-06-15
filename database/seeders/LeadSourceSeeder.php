<?php

namespace Database\Seeders;

use App\Models\LeadSource;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeadSourceSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        $leadSources = [
            __('Website'),
            __('Online AD'),
            __('Twitter'),
            __('LinkedIn'),
            __('Webinar'),
            __('Trade Show'),
            __('Referral'),
        ];

        foreach ($leadSources as $leadSource) {
            LeadSource::create(['name' => $leadSource]);
        }
    }
}
