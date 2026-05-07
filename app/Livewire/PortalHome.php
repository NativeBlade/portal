<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use NativeBlade\Facades\NativeBlade;
use NativeBlade\Plugins\Scan;

#[Layout('components.layouts.app')]
class PortalHome extends Component
{
    public bool $canScan = false;

    public function mount(): void
    {
        $this->canScan = NativeBlade::isMobile();
    }

    public function scanQr()
    {
        return NativeBlade::scan(function (Scan $s) {
            $s->id('portal-url');
            $s->formats(['QR_CODE']);
        })->toResponse();
    }

    #[On('nb:scan')]
    public function onScan($result = null, $id = null)
    {
        if ($id !== 'portal-url' || !$result) {
            return;
        }

        $content = $result['content'] ?? null;
        if (!$content) {
            return;
        }

        $this->dispatch('nb:portal-scanned', url: $content);
    }

    public function render()
    {
        return view('livewire.portal-home');
    }
}
