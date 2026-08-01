<?php

declare(strict_types=1);

namespace App\View\Components\Layout;

use Illuminate\View\Component;
use Illuminate\View\View;

class App extends Component
{
    public function __construct(
        public string $title = 'PACIPA - MGP',
        public ?string $description = null,
        public ?string $icon = null,
    ) {}

    public function render(): View
    {
        return view('components.layout.app');
    }
}
