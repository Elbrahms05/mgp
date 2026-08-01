<?php

declare(strict_types=1);

namespace App\View\Components\Documentation;

use App\Domains\Documentation\Models\PageGuide;
use Illuminate\View\Component;
use Illuminate\View\View;

class HelpPanel extends Component
{
    public ?PageGuide $guide;

    public function __construct(public ?string $route = null)
    {
        try {
            $this->guide = $route
                ? PageGuide::where('route_name', $route)->where('is_active', true)->first()
                : null;
        } catch (\Throwable) {
            $this->guide = null;
        }
    }

    public function render(): View
    {
        return view('components.documentation.help-panel');
    }
}
