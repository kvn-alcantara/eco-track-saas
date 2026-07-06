<?php

namespace App\Enums;

enum WasteType: string
{
    case General = 'general';
    case Recyclable = 'recyclable';
    case Organic = 'organic';
    case Hazardous = 'hazardous';
    case Electronic = 'electronic';
}
