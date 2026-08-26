<?php

namespace App\Services\Estimates;

use App\Models\Estimate;

class QuoteNumberGenerator
{
    private const PREFIX = 'Q-';

    private const PADDING = 6;

    public function generate(): string
    {
        $highestSequence = Estimate::query()
            ->where('quote_number', 'like', self::PREFIX.'%')
            ->lockForUpdate()
            ->pluck('quote_number')
            ->reduce(function (int $highestSequence, ?string $quoteNumber): int {
                if (! is_string($quoteNumber) || ! preg_match('/^Q-(\d{6})$/', $quoteNumber, $matches)) {
                    return $highestSequence;
                }

                return max($highestSequence, (int) $matches[1]);
            }, 0);

        return self::PREFIX.str_pad((string) ($highestSequence + 1), self::PADDING, '0', STR_PAD_LEFT);
    }
}
