<?php

namespace App\View\Components\Layouts;

use Illuminate\View\Component;

class Auth extends Component
{
    public function __construct(
        public ?string $title = null
    ) {}

    public function render()
    {
        return view('layouts.auth');
    }
}