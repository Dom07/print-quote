<?php

namespace App\Http\Controllers;

use App\Http\Requests\EstimateSandboxRequest;
use App\Services\Estimates\PaperWeightCalculator;
use Illuminate\View\View;

class EstimateSandboxController extends Controller
{
    public function create(): View
    {
        return view('estimate-sandbox');
    }

    public function store(EstimateSandboxRequest $request, PaperWeightCalculator $calculator): View
    {
        $validated = $request->validated();

        return view('estimate-sandbox', [
            'input' => $validated,
            'result' => [
                'no_of_sheets' => $calculator->forNoOfSheets($validated),
                'no_of_sheets_with_wastage' => $calculator->forNoOfSheetsWithWastage($validated),
                'no_of_sheets_to_process' => $calculator->forNoOfSheetsToProcess($validated),
            ],
        ]);
    }
}
