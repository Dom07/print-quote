<?php

namespace App\Enums;

enum EstimateStatus: string
{
    case Draft = 'draft';
    case Calculated = 'calculated';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
