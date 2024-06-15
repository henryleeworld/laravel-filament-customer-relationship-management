<?php

namespace Database\Seeders;

use App\Models\PipelineStage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PipelineStageSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        $pipelineStages = [
            [
                'name' => __('Lead'),
                'position' => 1,
                'is_default' => true,
            ],
            [
                'name' => __('Contact Made'),
                'position' => 2,
            ],
            [
                'name' => __('Proposal Made'),
                'position' => 3,
            ],
            [
                'name' => __('Proposal Rejected'),
                'position' => 4,
            ],
            [
                'name' => __('Customer'),
                'position' => 5,
            ]
        ];

        foreach ($pipelineStages as $stage) {
            PipelineStage::create($stage);
        }
    }
}
