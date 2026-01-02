<?php

namespace App\Livewire;

use App\Livewire\Components\SearchableSelect;
use Livewire\Component;

class SearchableSelectDemo extends Component
{
    public $selectedOption = null;
    public $selectedMultiple = [];
    public $countries = [];
    
    public function mount()
    {
        $this->countries = [
            ['value' => 'us', 'label' => 'United States'],
            ['value' => 'ca', 'label' => 'Canada'],
            ['value' => 'uk', 'label' => 'United Kingdom'],
            ['value' => 'de', 'label' => 'Germany'],
            ['value' => 'fr', 'label' => 'France'],
            ['value' => 'jp', 'label' => 'Japan'],
            ['value' => 'au', 'label' => 'Australia'],
            ['value' => 'br', 'label' => 'Brazil'],
            ['value' => 'in', 'label' => 'India'],
            ['value' => 'cn', 'label' => 'China'],
            ['value' => 'mx', 'label' => 'Mexico'],
            ['value' => 'it', 'label' => 'Italy'],
            ['value' => 'es', 'label' => 'Spain'],
            ['value' => 'kr', 'label' => 'South Korea'],
            ['value' => 'nl', 'label' => 'Netherlands'],
        ];
    }
    
    public function render()
    {
        return view('livewire.searchable-select-demo');
    }
}