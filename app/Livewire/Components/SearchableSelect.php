<?php

namespace App\Livewire\Components;

use Livewire\Component;

class SearchableSelect extends Component
{
    public $options = [];
    public $placeholder = 'Select an option...';
    public $searchPlaceholder = 'Search...';
    public $wireModel;
    public $disabled = false;
    public $error = false;
    public $multiple = false;
    public $searchable = true;
    public $allowClear = true;
    public $maxHeight = '200px';
    public $emptyMessage = 'No options found';
    
    protected $listeners = ['refreshSearchableSelect' => 'refreshOptions'];

    public function mount($options = [], $placeholder = null, $searchPlaceholder = null, $wireModel = null, $disabled = false, $error = false, $multiple = false, $searchable = true, $allowClear = true, $maxHeight = null, $emptyMessage = null)
    {
        $this->options = $options;
        $this->placeholder = $placeholder ?? $this->placeholder;
        $this->searchPlaceholder = $searchPlaceholder ?? $this->searchPlaceholder;
        $this->wireModel = $wireModel;
        $this->disabled = $disabled;
        $this->error = $error;
        $this->multiple = $multiple;
        $this->searchable = $searchable;
        $this->allowClear = $allowClear;
        $this->maxHeight = $maxHeight ?? $this->maxHeight;
        $this->emptyMessage = $emptyMessage ?? $this->emptyMessage;
    }

    public function refreshOptions($options)
    {
        $this->options = $options;
    }

    public function render()
    {
        return view('components.searchable-select');
    }
}