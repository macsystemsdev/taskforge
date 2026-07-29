<?php

namespace App\Filament\Resources\SubscriptionPlans\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\Hidden;

class SubscriptionPlanMetadataForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Hidden::make('plan_preview'),
                /*
                |--------------------------------------------------------------------------
                | Customer-facing Identity
                |--------------------------------------------------------------------------
                |
                | These fields control how the subscription plan is presented to
                | customers. They never affect the underlying commercial contract.
                |
                */

                TextInput::make('display_name')
                    ->label('Display Name')
                    ->required()
                    ->maxLength(255)
                    ->live(debounce: 300),

                TextInput::make('subtitle')
                    ->maxLength(255)
                    ->live(debounce: 300),

                Textarea::make('description')
                    ->rows(3)
                    ->maxLength(1000)
                    ->live(debounce: 500),

                /*
                |--------------------------------------------------------------------------
                | Marketing
                |--------------------------------------------------------------------------
                */

                TextInput::make('badge')
                    ->maxLength(30)
                    ->helperText('Examples: Most Popular, Best Value')
                    ->live(),

                TextInput::make('button_text')
                    ->maxLength(40)
                    ->default('Get Started')
                    ->live(debounce: 300),

                Textarea::make('marketing_copy')
                    ->rows(4)
                    ->maxLength(2000)
                    ->live(debounce: 300),

                /*
                |--------------------------------------------------------------------------
                | Display
                |--------------------------------------------------------------------------
                |
                | TODO:
                | Replace the simple preview with the shared pricing card component
                | used by the public pricing page so administrators preview the
                | exact customer experience.
                |
                */

                ColorPicker::make('accent_color')
                    ->live(),

                Toggle::make('popular')
                    ->live(),

                Toggle::make('recommended')
                    ->live(),

                TextInput::make('card_order')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->live(),

                ViewField::make('preview')
                    ->view('filament.resources.subscription-plans.preview')
                    ->dehydrated(false)
                    ->viewData(fn(callable $get) => [
                        'plan' => $get('plan_preview'),

                        'metadata' => [
                            'display_name' => $get('display_name'),
                            'subtitle' => $get('subtitle'),
                            'description' => $get('description'),
                            'badge' => $get('badge'),
                            'button_text' => $get('button_text'),
                            'marketing_copy' => $get('marketing_copy'),
                            'accent_color' => $get('accent_color'),
                            'popular' => $get('popular'),
                            'recommended' => $get('recommended'),
                            'card_order' => $get('card_order'),
                        ],
                    ])
            ]);
    }
}
