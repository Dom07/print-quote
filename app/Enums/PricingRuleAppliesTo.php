<?php

namespace App\Enums;

enum PricingRuleAppliesTo: string
{
    case Quantity = 'quantity';
    case ProductionSheets = 'production_sheets';
    case SheetsUsedForProcess = 'sheets_used_for_process';
    case TotalSheetsWithWastage = 'total_sheets_with_wastage';
}
