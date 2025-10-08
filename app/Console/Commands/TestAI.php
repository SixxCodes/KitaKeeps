<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Actions\ForecastAIGenerator;
use OpenAI\Laravel\Facades\OpenAI;

class TestAI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-a-i';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test OpenAI output in terminal';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response = OpenAI::responses()->create([
            'model' => 'gpt-5',
            'input' => 'Hello! Show me a test forecast.',
        ]);

        $this->info($response->outputText);
    }
}