<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\PipelineStage;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPipelineStage = PipelineStage::where('is_default', true)->first()->id;
        $allStages = PipelineStage::pluck('id');
        $allEmployees = User::where('role_id', Role::where('name', 'Employee')->first()->id)->pluck('id');
        Customer::factory()
            ->count(10)
            ->has(Task::factory()->count(3))
            ->create([
                'pipeline_stage_id' => $defaultPipelineStage,
            ])
            ->each(function (Customer $customer) use ($allEmployees, $allStages) {
                $customer->pipeline_stage_id = $allStages->random();
                $customer->employee_id = $allEmployees->random();
                $customer->save();
                $customer->tags()->attach(random_int(1, 2));
            });
    }
}
