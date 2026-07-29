<?php

namespace App\Data\SubscriptionPlan;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class UpdateSubscriptionPlanMetadataData extends Data
{
    public function __construct(

        #[MapInputName('display_name')]
        public string $displayName,

        public ?string $subtitle = null,

        public ?string $description = null,

        public ?string $badge = null,

        public bool $popular = false,

        public bool $recommended = false,

        #[MapInputName('accent_color')]
        public ?string $accentColor = null,

        #[MapInputName('card_order')]
        public int $cardOrder = 0,

        #[MapInputName('button_text')]
        public ?string $buttonText = null,

        #[MapInputName('marketing_copy')]
        public ?string $marketingCopy = null,
    ) {}
}
