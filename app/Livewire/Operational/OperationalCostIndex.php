<?php

namespace App\Livewire\Operational;

use App\Models\OperationalCost;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Operational Cost')]
class OperationalCostIndex extends Component
{
    use WithPagination, WithFileUploads;

    public $title, $amount, $date, $attachment;
    public $editingId = null;
    public $isEdit = false; 
    
    public $search = '';

    protected $rules = [
        'title' => 'required|min:3',
        'amount' => 'required|numeric',
        'date' => 'required|date',
        'attachment' => 'nullable|max:2048', 
    ];

    public function resetForm()
    {
        $this->reset(['title', 'amount', 'date', 'attachment', 'editingId', 'isEdit']);
        $this->resetErrorBag();
    }

    private function uploadToApi($file)
    {
        $client = new \GuzzleHttp\Client();

        $response = $client->post(
            config('services.file_upload.api_url') . '/file/upload',
            [
                'multipart' => [
                    [
                        'name'     => 'file',
                        'contents' => fopen($file->getRealPath(), 'r'),
                        'filename' => $file->getClientOriginalName(),
                    ],
                ],
            ]
        );

        if ($response->getStatusCode() !== 200) {
            throw new \Exception(
                'Upload attachment gagal. Status: ' . $response->getStatusCode()
            );
        }

        $body = json_decode($response->getBody()->getContents(), true);

        $url = $body['data']['file_url'] ?? null;

        if (! $url) {
            throw new \Exception('file_url tidak ditemukan di response API');
        }

        return $url;
    }

    public function edit($id)
    {
        $this->resetForm();
        $cost = OperationalCost::findOrFail($id);
        
        $this->editingId = $id;
        $this->isEdit = true;
        
        $this->title = $cost->title;
        $this->amount = $cost->amount;
        $this->date = \Carbon\Carbon::parse($cost->date)->format('Y-m-d');
        $this->attachment = $cost->attachments_url;

        $this->modal('cost-modal')->show();
    }

    public function save()
    {
        $this->validate();

        $cleanAmount = (int) str_replace('.', '', $this->amount);

        $data = [
            'title' => $this->title,
            'amount' => $cleanAmount,
            'date' => $this->date,
            'created_by' => Auth::id(),
        ];

        if ($this->attachment && ! is_string($this->attachment)) {
            $data['attachments_url'] = $this->uploadToApi($this->attachment);
        }

        if ($this->editingId) {
            OperationalCost::find($this->editingId)->update($data);
        } else {
            OperationalCost::create($data);
        }

        $this->modal('cost-modal')->close();
        $this->resetForm();
    }

    public function delete($id)
    {
        OperationalCost::findOrFail($id)->delete();
    }

    public function render()
    {
        return view('livewire.operational.operational-cost-index', [
            'costs' => OperationalCost::with('creator')
                ->where('title', 'ilike', '%' . $this->search . '%')
                ->orderBy('date', 'desc')
                ->paginate(10)
        ]);
    }
}