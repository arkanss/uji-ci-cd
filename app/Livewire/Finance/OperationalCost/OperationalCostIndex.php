<?php

namespace App\Livewire\Finance\OperationalCost;

use App\Models\OperationalCost;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;

#[Title('Operational Cost')]
class OperationalCostIndex extends Component
{
    use WithPagination;

    public $search = '';

    public function resetForm()
    {
        // kept for backward compatibility but no longer used
    }


    public function delete($id)
    {
        OperationalCost::findOrFail($id)->delete();
    }

    public function render()
    {
        $query = OperationalCost::with('creator')
            ->select('id', 'title', 'amount', 'attachments_url', 'date', 'created_by')
            ->orderBy('date', 'desc');

        $search = trim($this->search ?? '');

        if ($search !== '') {
            $driver = DB::getDriverName();
            if ($driver === 'pgsql') {
                $query->where('title', 'ilike', "%{$search}%");
            } else {
                $query->where('title', 'like', "%{$search}%");
            }
        }

        $costs = $query->Paginate(10);

        return view('livewire.finance.operational-cost.operational-cost-index', compact('costs'))->layout('layouts.app', [
            'breadcrumbs' => breadcrumbs(
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Finance', 'url' => '#'],
                ['label' => 'Operational Costs', 'url' => '']
            ),
        ]);
    }
}
