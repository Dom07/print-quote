<?php

namespace App\Http\Controllers;

use App\Http\Requests\EstimateSandboxRequest;
use App\Models\PricingItem;
use App\Services\Estimates\DripOffCalculator;
use App\Services\Estimates\LaminationCalculator;
use App\Services\Estimates\PaperPricingCalculator;
use App\Services\Estimates\PaperWeightCalculator;
use App\Services\Estimates\PunchingRateResolver;
use App\Services\Estimates\SpotUvCalculator;
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

        return view('estimate-sandbox', [
            'input' => $validated,
            'paperOptions' => $this->pricingOptions('paper'),
            'interestOptions' => $this->pricingOptions('interest-slabs'),
            'punchingOptions' => $this->pricingOptions('punching'),
            'laminationOptions' => $this->pricingOptions('lamination', ['BOPP Lamination', 'Matte Lamination']),
            'spotUvOptions' => $this->pricingOptions('spot-uv', ['Spot UV', 'Raised UV']),
            'result' => $kgResult,
            'paperPricingResult' => $pricingCalculator->calculate(
                selectedPaperRate: (float) $selectedPaperItem->rate,
                interestPercentage: (float) $selectedInterestItem->rate,
                kgsOfOrder: $kgResult['no_of_sheets_with_wastage'],
                noOfSheets: (int) $validated['no_of_sheets'],
            ),
            'selectedPaperItem' => $selectedPaperItem,
            'selectedInterestItem' => $selectedInterestItem,
            'selectedPunchingItem' => $selectedPunchingItem,
            'selectedFrontLaminationItem' => $selectedFrontLaminationItem,
            'selectedBackLaminationItem' => $selectedBackLaminationItem,
            'selectedSpotUvItem' => $selectedSpotUvItem,
            'punchingRateResult' => $needsPunching
                ? $punchingRateResolver->resolve(
                    pricingItem: $selectedPunchingItem,
                    quantity: (int) $validated['no_of_sheets'],
                )
                : null,
            'laminationResult' => $needsLamination
                ? $laminationCalculator->calculate(
                    mode: $validated['lamination_mode'],
                    frontPricingItem: $selectedFrontLaminationItem,
                    backPricingItem: $selectedBackLaminationItem,
                    quantity: (int) $validated['no_of_sheets_to_process'],
                    length: (float) $validated['length'],
                    width: (float) $validated['width'],
                )
                : null,
            'spotUvResult' => $needsSpotUv
                ? $spotUvCalculator->calculate(
                    pricingItem: $selectedSpotUvItem,
                    quantity: (int) $validated['no_of_sheets_to_process'],
                )
                : null,
            'dripOffResult' => $needsDripOff
                ? $dripOffCalculator->calculate(
                    length: (float) $validated['length'],
                    width: (float) $validated['width'],
                    quantity: (int) $validated['no_of_sheets_to_process'],
                )
                : null,
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
