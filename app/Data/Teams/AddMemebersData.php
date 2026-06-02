<?php

namespace App\Data\Teams;

class AddMemebersData
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public array $member_ids,
    ) {
        
    }
}
