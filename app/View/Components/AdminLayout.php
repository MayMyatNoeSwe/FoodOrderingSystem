<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AdminLayout extends Component
{
    /**
     * The active sidebar item key.
     */
    public ?string $active;

    /**
     * The page title.
     */
    public ?string $title;

    /**
     * The topbar page heading.
     */
    public ?string $heading;

    /**
     * The topbar page subheading.
     */
    public ?string $subheading;

    /**
     * Create a new component instance.
     */
    public function __construct(
        ?string $active = 'dashboard',
        ?string $title = null,
        ?string $heading = null,
        ?string $subheading = null
    ) {
        $this->active = $active;
        $this->title = $title;
        $this->heading = $heading;
        $this->subheading = $subheading;
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.admin');
    }
}
