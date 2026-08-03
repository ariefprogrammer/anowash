<?php

namespace App\Livewire\Marketing;

use App\Models\Owner;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.marketing')]
class Dashboard extends Component
{
    public function render()
    {
        $marketing = Auth::user()->marketing;

        $owners = Owner::where('referred_by_marketing_id', $marketing->id)
            ->withCount('outlets')
            ->latest()
            ->get();

        $commissionPaid = $marketing->commissions()->where('status', 'paid')->sum('amount');
        $commissionUnpaid = $marketing->commissions()->where('status', 'unpaid')->sum('amount');

        return view('livewire.marketing.dashboard', [
            'marketing' => $marketing,
            'owners' => $owners,
            'totalCustomers' => $owners->count(),
            'commissionPaid' => $commissionPaid,
            'commissionUnpaid' => $commissionUnpaid,
        ]);
    }
}