<?php

namespace App\Livewire\DPItemRequest;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\WorkerInventoryRequests;
use App\Enums\DPItemRequestsEnum;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.app')]
#[Title('DP Item Requests')]
class DPItemRequestIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    protected $casts = [
        'status' => \App\Enums\DPItemRequestsEnum::class,
    ];

    public function render()
    {
        return view('livewire.d-p-item-request.d-p-item-request-index', [
            'requests' => WorkerInventoryRequests::with('user')
                ->latest()
                ->paginate(10),
        ]);
    }
}
