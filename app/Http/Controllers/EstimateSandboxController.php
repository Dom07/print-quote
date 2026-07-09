<?php

namespace App\Http\Controllers;

use App\Http\Requests\EstimateSandboxRequest;
use App\Models\PricingItem;
use App\Services\Estimates\DripOffCalculator;
use App\Services\Estimates\LaminationCalculator;
use App\Services\Estimates\MarginCalculator;
use App\Services\Estimates\PaperPricingCalculator;
use App\Services\Estimates\PaperWeightCalculator;
use App\Services\Estimates\PieceLevelAddonResolver;
use App\Services\Estimates\PiecePricingCalculator;
use App\Services\Estimates\PunchingRateResolver;
use App\Services\Estimates\RequiredPieceCostResolver;
use App\Services\Estimates\SpotUvCalculator;
use App\Services\Estimates\TotalPricePerSheetCalculator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

class EstimateSandboxController extends Controller
{
    public function create(): View
    {
        return view('estimate-sandbox', [
            'paperOptions' => $this->pricingOptions('paper'),
            'interestOptions' => $this->pricingOptions('interest-slabs'),
            'punchingOptions' => $this->pricingOptions('punching'),
            'laminationOptions' => $this->pricingOptions('lamination', ['BOPP Lamination', 'Matte Lamination']),
            'spotUvOptions' => $this->pricingOptions('spot-uv', ['Spot UV', 'Raised UV']),
        ]);
    }

    public function store(
        EstimateSandboxRequest $request,
        PaperWeightCalculator $weightCalculator,
        PaperPricingCalculator $pricingCalculator,
        PunchingRateResolver $punchingRateResolver,
        LaminationCalculator $laminationCalculator,
        SpotUvCalculator $spotUvCalculator,
        DripOffCalculator $dripOffCalculator,
        TotalPricePerSheetCalculator $totalPricePerSheetCalculator,
        PieceLevelAddonResolver $pieceLevelAddonResolver,
        RequiredPieceCostResolver $requiredPieceCostResolver,
        PiecePricingCalculator $piecePricingCalculator,
        MarginCalculator $marginCalculator,
    ): View {
        $validated = $request->validated();

        $kgResult = [
            'no_of_sheets' => $weightCalculator->forNoOfSheets($validated),
            'no_of_sheets_with_wastage' => $weightCalculator->forNoOfSheetsWithWastage($validated),
            'no_of_sheets_to_process' => $weightCalculator->forNoOfSheetsToProcess($validated),
        ];

        $selectedPaperItem = PricingItem::findOrFail($validated['paper_pricing_item_id']);
        $selectedInterestItem = PricingItem::findOrFail($validated['interest_pricing_item_id']);
        $needsPunching = (bool) $validated['needs_punching'];
        $needsLamination = (bool) $validated['needs_lamination'];
        $needsSpotUv = (bool) $validated['needs_spot_uv'];
        $needsDripOff = (bool) $validated['needs_drip_off'];
        $needsLaceCost = (bool) $validated['needs_lace_cost'];
        $punchCostJobType = $validated['punch_cost_job_type'];
        $selectedPunchingItem = $needsPunching && isset($validated['punching_pricing_item_id'])
            ? PricingItem::findOrFail($validated['punching_pricing_item_id'])
            : null;
        $selectedFrontLaminationItem = $needsLamination && isset($validated['lamination_front_pricing_item_id'])
            ? PricingItem::findOrFail($validated['lamination_front_pricing_item_id'])
            : null;
        $selectedBackLaminationItem = $needsLamination && isset($validated['lamination_back_pricing_item_id'])
            ? PricingItem::findOrFail($validated['lamination_back_pricing_item_id'])
            : null;
        $selectedSpotUvItem = $needsSpotUv && isset($validated['spot_uv_pricing_item_id'])
            ? PricingItem::findOrFail($validated['spot_uv_pricing_item_id'])
            : null;
        $paperPricingResult = $pricingCalculator->calculate(
            selectedPaperRate: (float) $selectedPaperItem->rate,
            interestPercentage: (float) $selectedInterestItem->rate,
            kgsOfOrder: $kgResult['no_of_sheets_with_wastage'],
            noOfSheets: (int) $validated['no_of_sheets'],
        );
        $punchingRateResult = $needsPunching
            ? $punchingRateResolver->resolve(
                pricingItem: $selectedPunchingItem,
                quantity: (int) $validated['no_of_sheets'],
            )
            : null;
        $laminationResult = $needsLamination
            ? $laminationCalculator->calculate(
                mode: $validated['lamination_mode'],
                frontPricingItem: $selectedFrontLaminationItem,
                backPricingItem: $selectedBackLaminationItem,
                quantity: (int) $validated['no_of_sheets_to_process'],
                length: (float) $validated['length'],
                width: (float) $validated['width'],
            )
            : null;
        $spotUvResult = $needsSpotUv
            ? $spotUvCalculator->calculate(
                pricingItem: $selectedSpotUvItem,
                quantity: (int) $validated['no_of_sheets_to_process'],
            )
            : null;
        $dripOffResult = $needsDripOff
            ? $dripOffCalculator->calculate(
                length: (float) $validated['length'],
                width: (float) $validated['width'],
                quantity: (int) $validated['no_of_sheets_to_process'],
            )
            : null;
        $totalPricePerSheetResult = $totalPricePerSheetCalculator->calculate(
            paperPricePerSheet: $paperPricingResult['price_per_sheet'],
            printingCost: (float) $validated['printing_cost'],
            inkCost: (float) $validated['ink_cost'],
            foilingCost: isset($validated['foiling_cost']) ? (float) $validated['foiling_cost'] : null,
            punchingRate: $punchingRateResult['rate'] ?? null,
            laminationValue: $laminationResult['combined_value'] ?? null,
            spotUvValue: $spotUvResult['value'] ?? null,
            dripOffRate: $dripOffResult['final_rate_per_sheet'] ?? null,
        );
        $piecePricingResult = $piecePricingCalculator->calculate(
            noOfSheets: (int) $validated['no_of_sheets'],
            ups: (int) $validated['ups'],
            totalPricePerSheet: $totalPricePerSheetResult['total_price_per_sheet'],
            windowLaborCost: isset($validated['window_labor_cost']) ? (float) $validated['window_labor_cost'] : null,
            laceCost: $needsLaceCost ? $pieceLevelAddonResolver->laceCost() : null,
            designingCostRate: $pieceLevelAddonResolver->designingCost(),
            punchCostJobType: $punchCostJobType,
            repeatJobPunchCost: $punchCostJobType === 'repeat_job' ? $requiredPieceCostResolver->repeatJobPunchCost() : null,
            newJobPunchCost: isset($validated['new_job_punch_cost']) ? (float) $validated['new_job_punch_cost'] : null,
            expenses: (float) $validated['expenses'],
        );
        $marginResult = $marginCalculator->calculate($piecePricingResult['total_cost']);

        return view('estimate-sandbox', [
            'input' => $validated,
            'paperOptions' => $this->pricingOptions('paper'),
            'interestOptions' => $this->pricingOptions('interest-slabs'),
            'punchingOptions' => $this->pricingOptions('punching'),
            'laminationOptions' => $this->pricingOptions('lamination', ['BOPP Lamination', 'Matte Lamination']),
            'spotUvOptions' => $this->pricingOptions('spot-uv', ['Spot UV', 'Raised UV']),
            'result' => $kgResult,
            'paperPricingResult' => $paperPricingResult,
            'selectedPaperItem' => $selectedPaperItem,
            'selectedInterestItem' => $selectedInterestItem,
            'selectedPunchingItem' => $selectedPunchingItem,
            'selectedFrontLaminationItem' => $selectedFrontLaminationItem,
            'selectedBackLaminationItem' => $selectedBackLaminationItem,
            'selectedSpotUvItem' => $selectedSpotUvItem,
            'punchingRateResult' => $punchingRateResult,
            'laminationResult' => $laminationResult,
            'spotUvResult' => $spotUvResult,
            'dripOffResult' => $dripOffResult,
            'totalPricePerSheetResult' => $totalPricePerSheetResult,
            'piecePricingResult' => $piecePricingResult,
            'marginResult' => $marginResult,
        ]);
    }

    private function pricingOptions(string $categorySlug, array $allowedNames = []): Collection
    {
        $query = PricingItem::query()
            ->where('is_active', true)
            ->where('is_selectable', true)
            ->whereHas('pricingCategory', function ($query) use ($categorySlug) {
                $query
                    ->where('slug', $categorySlug)
                    ->where('is_active', true);
            })
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($allowedNames !== []) {
            $query->whereIn('name', $allowedNames);
        }

        return $query->get();
    }
}
