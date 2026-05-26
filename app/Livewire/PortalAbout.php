<?php

namespace App\Livewire;

use App\Providers\AppServiceProvider;
use Livewire\Attributes\Layout;
use Livewire\Component;
use NativeBlade\Facades\NativeBlade;

#[Layout('components.layouts.app')]
class PortalAbout extends Component
{
    public string $platform = 'desktop';
    public string $version = '1.0.0';

    public function mount(): void
    {
        $this->platform = NativeBlade::platform();
        $this->version = AppServiceProvider::VERSION;
    }

    public function render()
    {
        return view('livewire.portal-about');
    }
}
