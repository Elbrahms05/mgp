<?php

declare(strict_types=1);

namespace App\View\Components\Layout;

use Illuminate\View\Component;
use Illuminate\View\View;

class Topbar extends Component
{
    public function render(): View
    {
        return view('components.layout.topbar');
    }
}
