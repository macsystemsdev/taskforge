<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class Reporting extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartPie;
    
    protected static ?string $navigationLabel = 'Reporting';
    
    protected static ?string $title = 'Reporting & Analytics';
    
    protected static ?string $slug = 'reporting';
    
    protected static ?int $navigationSort = 2;
    
    protected string $view = 'filament.pages.reporting';
}
