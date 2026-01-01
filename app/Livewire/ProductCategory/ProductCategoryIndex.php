<?php

namespace App\Livewire\ProductCategory;

use App\Models\ProductCategory;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
#[Title('Product Categories')]
class ProductCategoryIndex extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $name = '';
    public $description = '';
    public $image;
    public $editingCategoryId = null;
    public $oldImage = null;
    public $categoryIdBeingDeleted = null;

    protected $queryString = ['search' => ['except' => '']];

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
                'Upload image ke API gagal. Status: ' . $response->getStatusCode()
            );
        }

        $body = json_decode($response->getBody()->getContents(), true);

        $url = $body['data']['file_url'] ?? null;

        if (! $url) {
            throw new \Exception('file_url tidak ditemukan di response API');
        }

        return $url;
    }

    public function create()
    {
        $this->resetForm();
        $this->modal('category-modal')->show();
    }

    public function resetForm()
    {
        $this->reset(['name', 'description', 'image', 'editingCategoryId', 'oldImage', 'categoryIdBeingDeleted']);
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate([
            'name' => [
                'required',
                'min:3',
                'unique:product_categories,name,' . ($this->editingCategoryId ?: 'NULL') . ',id',
            ],
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:1024',
        ]);

        DB::transaction(function () {
            $data = [
                'name' => $this->name,
                'description' => $this->description,
            ];

            if ($this->image) {
                $data['image'] = $this->uploadToApi($this->image);
            }

            if ($this->editingCategoryId) {
                ProductCategory::findOrFail($this->editingCategoryId)->update($data);
            } else {
                ProductCategory::create($data);
            }
        });

        $this->resetForm();
        $this->modal('category-modal')->close();
    }

    public function edit($id)
    {
        $this->resetForm();
        $category = ProductCategory::findOrFail($id);

        $this->editingCategoryId = $id;
        $this->name = $category->name;
        $this->description = $category->description;
        $this->oldImage = $category->image;

        $this->modal('category-modal')->show();
    }

    public function confirmDelete($id)
    {
        $this->categoryIdBeingDeleted = $id;
        $this->modal('delete-category-modal')->show();
    }

    public function delete()
    {
        if ($this->categoryIdBeingDeleted) {
            ProductCategory::findOrFail($this->categoryIdBeingDeleted)->delete();
            $this->modal('delete-category-modal')->close();
            $this->categoryIdBeingDeleted = null;
        }
    }

    public function render()
    {
        $query = ProductCategory::select('id', 'name', 'description', 'image', 'created_at', 'updated_at')
            ->orderBy('created_at', 'desc');

        $search = trim($this->search ?? '');
        if ($search !== '') {
            $driver = DB::getDriverName();
            $term = "%{$search}%";
            if ($driver === 'pgsql') {
                $query->where('name', 'ilike', $term);
            } else {
                $query->where('name', 'like', $term);
            }
        }

        $categories = $query->Paginate(10);

        return view('livewire.productcategory.category-index', compact('categories'));
    }
}
