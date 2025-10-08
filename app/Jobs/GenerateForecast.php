<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Actions\ForecastAIGenerator;
use App\Models\Branch;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateForecast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $branchId;

    /**
     * Create a new job instance.
     */
    public function __construct($branchId)
    {
       $this->branchId = $branchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ForecastAIGenerator::generateForBranch($this->branchId);
    }
}
