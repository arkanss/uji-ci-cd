<?php

namespace App\Livewire\Forms\Product;

use App\Models\Product;
use Livewire\Form;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use GuzzleHttp\Client;

class ProductForm extends Form
{
    public ?Product $product = null;

    public $name = '';
    public $price = 0;
    public $discount = 0;
    public $description = '';
    public $image;
    public $oldImage;
    public $point_value = 0;
    public $category_id = '';
    public $brand = '';
    public $code = '';
    public $sku = '';
    public $featured = false;
    public $stock = 0;
    public $reward_type = 0;
    // public $reward_reference_id = '';
    public $level_zero_reward = 0;
    public $level_one_reward = 0;
    public $scope_service = [];
    public $scope_type = [];
    public $weight = 0;
    public $height = 0;
    public $length = 0;
    public $width = 0;

    public function rules()
    {
        return [
            'name' => 'required|min:3',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'point_value' => 'nullable|integer',
            'category_id' => 'required|exists:product_categories,id',
            'brand' => 'nullable|string',
            'code' => 'nullable|string',
            'sku' => 'nullable|string',
            'featured' => 'boolean',
            'stock' => 'nullable|integer|min:0',
            'reward_type' => 'nullable|integer',
            // 'reward_reference_id' => 'nullable|string',
            'level_zero_reward' => 'nullable|integer',
            'level_one_reward' => 'nullable|integer',
            'scope_service' => 'nullable|array',
            'scope_type' => 'nullable|array',
            'weight' => 'nullable|integer',
            'height' => 'nullable|integer',
            'length' => 'nullable|integer',
            'width' => 'nullable|integer',
        ];
    }

    public function setProduct(Product $product)
    {
        $this->product = $product;
        
        $this->name = $product->name;
        $this->price = $product->price;
        $this->discount = $product->discount;
        $this->description = $product->description;
        $this->oldImage = $product->image;
        $this->point_value = $product->point_value;
        $this->category_id = $product->category_id;
        $this->brand = $product->brand;
        $this->code = $product->code;
        $this->sku = $product->sku;
        $this->featured = (bool)$product->featured;
        $this->stock = $product->stock;
        $this->reward_type = $product->reward_type;
        // $this->reward_reference_id = $product->reward_reference_id;
        $this->level_zero_reward = $product->level_zero_reward;
        $this->level_one_reward = $product->level_one_reward;
        $this->scope_service = $product->scope_service ?? [];
        $this->scope_type = $product->scope_type ?? [];
        $this->weight = $product->weight;
        $this->height = $product->height;
        $this->length = $product->length;
        $this->width = $product->width;
    }

    private function uploadToApi($file): string
    {
        $client = new Client();

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


    public function store()
    {
        $this->validate();

        $data = $this->except(['product', 'image', 'oldImage']);
        
        if ($this->image) {
            $data['image'] = $this->uploadToApi($this->image);
        }

        Product::create($data);
        $this->reset(); 
    }

    public function update()
    {
        $this->validate();

        $data = $this->except(['product', 'image', 'oldImage']);

        if ($this->image) {
            $data['image'] = $this->uploadToApi($this->image);
        }

        $this->product->update($data);
        $this->reset();
    }
}