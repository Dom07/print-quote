<?php

namespace App\Http\Controllers;

use App\Http\Requests\EstimateSandboxRequest;
use App\Models\PricingItem;
use App\Services\Estimates\PaperPricingCalculator;
use App\Services\Estimates\PaperWeightCalculator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

class EstimateSandboxController extends Controller
{
    public function create(): View
    {
        return view('estimate-sandbox', [
            'paperOptions' => $this->pricingOptions('paper'),
            'interestOptions' => $this->pricingOptions('interest-slabs'),
        ]);
    }

    public function store(
        EstimateSandboxRequest $request,
        PaperWeightCalculator $weightCalculator,
        PaperPricingCalculator $pricingCalculator,
    ): View {
        $validated = $request->validated();

        $kgResult = [
            'no_of_sheets' => $weightCalculator->forNoOfSheets($validated),
            'no_of_sheets_with_wastage' => $weightCalculator->forNoOfSheetsWithWastage($validated),
            'no_of_sheets_to_process' => $weightCalculator->forNoOfSheetsToProcess($validated),
        ];

        $selectedPaperItem = PricingItem::findOrFail($validated['paper_pricing_item_id']);
        $selectedInterestItem = PricingItem::findOrFail($validated['interest_pricing_item_id']);

        return view('estimate-sandbox', [
            'input' => $validated,
            'paperOptions' => $this->pricingOptions('paper'),
            'interestOptions' => $this->pricingOptions('interest-slabs'),
            'result' => $kgResult,
            'paperPricingResult' => $pricingCalculator->calculate(
                selectedPaperRate: (float) $selectedPaperItem->rate,
                interestPercentage: (float) $selectedInterestItem->rate,
                kgsOfOrder: $kgResult['no_of_sheets_with_wastage'],
                noOfSheets: (int) $validated['no_of_sheets'],
            ),
            'selectedPaperItem' => $selectedPaperItem,
            'selectedInterestItem' => $selectedInterestItem,
        ]);
    }

    private function pricingOptions(string $categorySlug): Collection
    {
        return PricingItem::query()
            ->where('is_active', true)
            ->where('is_selectable', true)
            ->whereHas('pricingCategory', function ($query) use ($categorySlug) {
                $query
                    ->where('slug', $categorySlug)
                    ->where('is_active', true);
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
