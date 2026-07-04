<?php

namespace App\Enums;

enum PricingRuleAppliesTo: string
{
    case ProductionSheets = 'production_sheets';
    case TotalSheetsWithWastage = 'total_sheets_with_wastage';
    case SheetsUsedForProcess = 'sheets_used_for_process';
    case Pieces = 'pieces';
    case TotalValue = 'total_value';
}
