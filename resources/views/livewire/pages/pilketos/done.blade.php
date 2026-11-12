<?php

use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.pilketos.public.layout')] class extends Component {
    public function mount(): void
    {
        Session::forget(['pilketos.vote', 'pilketos.confirm']);
        $this->redirectRoute('pilketos.enter-token', navigate: true);
    }
}; ?>

<div></div>
