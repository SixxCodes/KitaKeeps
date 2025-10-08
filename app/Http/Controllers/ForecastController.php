<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\Branch;

class ForecastController extends Controller
{
    public function show($branchId)
    {
        $branch = Branch::findOrFail($branchId);
        $filePath = "forecasts/branch_{$branchId}.txt";

        $forecastText = Storage::exists($filePath)
            ? Storage::get($filePath)
            : "No AI forecast has been generated yet for this branch.";

        return view('forecast.show', compact('branch', 'forecastText'));
    }
}
