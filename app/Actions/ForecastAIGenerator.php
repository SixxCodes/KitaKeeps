<?php

namespace App\Actions;

use App\Models\Branch;
use App\Models\BranchProduct;
use Illuminate\Support\Facades\Storage;
use OpenAI\Laravel\Facades\OpenAI;

class ForecastAIGenerator
{
    public static function generateForBranch($branchId)
    {
        $branch = Branch::with(['branchproducts.product', 'branchproducts.forecasts'])->find($branchId);

        if (!$branch) {
            return;
        }

        // Gather product summaries
        $productSummaries = [];
        foreach ($branch->branchproducts as $bp) {
            $latestForecast = $bp->forecasts->sortByDesc('created_at')->first();

            $productSummaries[] = [
                'product_name' => $bp->product->prod_name ?? 'Unknown Product',
                'stock_qty' => $bp->stock_qty,
                'forecast_qty' => $latestForecast->forecast_qty ?? 0,
                'method' => $latestForecast->method ?? 'N/A',
            ];
        }

        // Create a natural-language prompt for OpenAI
        $prompt = "You are an inventory AI assistant. Generate a short AI-based sales forecast summary for branch '{$branch->branch_name}'.
        Each product has the following data:\n\n" . json_encode($productSummaries, JSON_PRETTY_PRINT) . "\n\n
        Describe expected sales and give short advice (e.g., restock suggestions or best sellers). Be concise and readable. In the end, give advice, act like you are predicting the future base on this sales.";

        // Send to OpenAI
        $response = OpenAI::responses()->create([
            'model' => 'gpt-5',
            'input' => $prompt,
        ]);

        // Extract AI text
        $aiText = $response->outputText ?? 'No forecast generated.';

        // Save forecast file per branch
        $fileName = "forecasts/branch_{$branchId}.txt";
        Storage::put($fileName, $aiText);

        return $aiText;
    }

}
