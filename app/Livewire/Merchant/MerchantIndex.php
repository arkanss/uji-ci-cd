<?php

namespace App\Livewire\Merchant;

use App\Models\User;
use App\Models\Merchant;
use App\Models\MerchantImage;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Validation\Rule;
use GuzzleHttp\Client;

#[Layout('layouts.app')]
#[Title('Merchant Management')]
class MerchantIndex extends Component
{
    use WithPagination, WithFileUploads;

    public $currentStep = 1;
    public bool $isEdit = false;
    public $merchantId;
    public $user_name, $user_email, $user_username, $user_phone, $user_password, $access_code;
    public bool $auth_using_access_code = false;
    public $name, $description, $date_of_birth, $gender = 1, $profile_picture;
    public $merchant_galleries = [];
    public $address, $full_address, $code;
    public $area_province_id, $area_city_id, $area_district_id, $area_sub_district_id;
    public $latitude = 0, $longitude = 0;
    public $open_time, $close_time;
    public $status = 1, $point_balance = 0, $land_level = 1;
    
    public function getLocationProperty()
    {
        return $this->latitude . ', ' . $this->longitude;
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


    public function addGalleryItem()
    {
        $this->merchant_galleries[] = ['image' => null, 'ordering' => count($this->merchant_galleries) + 1];
    }

    public function removeGalleryItem($index)
    {
        unset($this->merchant_galleries[$index]);
        $this->merchant_galleries = array_values($this->merchant_galleries);
    }

    public function nextStep()
    {
        $this->validate([
            'user_name' => 'required|string|max:255',
            'user_username' => ['required', Rule::unique('users', 'username')->ignore($this->merchantId)],
            'user_email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->merchantId)],
            'user_phone' => 'required',
            'user_password' => $this->isEdit ? 'nullable|min:6' : 'required|min:6',
            'access_code' => $this->auth_using_access_code ? 'required|max:20' : 'nullable',
        ]);
        $this->currentStep = 2;
    }

    public function edit($id)
    {
        $this->resetFields();
        $this->isEdit = true;
        $this->merchantId = $id;

        $merchant = Merchant::with('user')->where('user_id', $id)->firstOrFail();
        $user = $merchant->user;

        $this->user_name = $user->name;
        $this->user_username = $user->username;
        $this->user_email = $user->email;
        $this->user_phone = $user->phone_number;
        $this->auth_using_access_code = $user->auth_using_access_code;
        $this->access_code = $user->access_code;

        $this->name = $merchant->name;
        $this->description = $merchant->description;
        $this->date_of_birth = $merchant->date_of_birth ? $merchant->date_of_birth->format('Y-m-d') : null;
        $this->gender = $merchant->gender;
        $this->address = $merchant->address;
        $this->full_address = $merchant->full_address;
        $this->code = $merchant->code;
        $this->area_province_id = $merchant->area_province_id;
        $this->area_city_id = $merchant->area_city_id;
        $this->area_district_id = $merchant->area_district_id;
        $this->area_sub_district_id = $merchant->area_sub_district_id;
        $this->latitude = $merchant->latitude;
        $this->longitude = $merchant->longitude;
        $this->open_time = $merchant->open_time ? $merchant->open_time->format('H:i') : null;
        $this->close_time = $merchant->close_time ? $merchant->close_time->format('H:i') : null;
        $this->status = $merchant->status;
        $this->point_balance = $merchant->point_balance;
        $this->land_level = $merchant->land_level;

        $this->modal('merchant-form-modal')->show();
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|max:255',
            'address' => 'required',
            'full_address' => 'required',
            'code' => ['required', Rule::unique('merchants', 'code')->ignore($this->merchantId, 'user_id')],
            'profile_picture' => $this->isEdit ? 'nullable|image|max:2048' : 'required|image|max:2048',
            'open_time' => 'required',
            'close_time' => 'required',
        ]);

        DB::beginTransaction();
        try {
            if ($this->isEdit) {
                $merchant = Merchant::where('user_id', $this->merchantId)->firstOrFail();
            } else {
                $user = User::create([
                    'name' => $this->user_name,
                    'username' => $this->user_username,
                    'email' => $this->user_email,
                    'phone_number' => $this->user_phone,
                    'password_hash' => Hash::make($this->user_password),
                    'role' => 4, 
                    'auth_using_access_code' => $this->auth_using_access_code,
                    'access_code' => $this->auth_using_access_code ? $this->access_code : null,
                    'user_code' => 'USR-' . strtoupper(Str::random(10)),
                ]);
                $merchant = new Merchant();
                $merchant->user_id = $user->id;
            }

            if ($this->profile_picture) {
                $merchant->profile_picture = $this->uploadToApi($this->profile_picture);
            }

            $merchant->fill([
                'name' => $this->name,
                'code' => $this->code,
                'address' => $this->address,
                'full_address' => $this->full_address,
                'area_province_id' => $this->area_province_id ?: null,
                'area_city_id' => $this->area_city_id ?: null,
                'area_district_id' => $this->area_district_id ?: null,
                'area_sub_district_id' => $this->area_sub_district_id ?: null,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'location' => DB::raw("ST_GeogFromText('POINT({$this->longitude} {$this->latitude})')"),
                'open_time' => $this->open_time,
                'close_time' => $this->close_time,
                'gender' => (int) $this->gender,
                'date_of_birth' => $this->date_of_birth ?: null,
                'description' => $this->description,
                'status' => (int) $this->status,
                'point_balance' => $this->point_balance,
                'land_level' => (int) $this->land_level,
            ]);
            $merchant->save();

            if (!empty($this->merchant_galleries)) {
                foreach ($this->merchant_galleries as $item) {
                    if (
                        isset($item['image']) &&
                        $item['image'] instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile
                    ) {
                        MerchantImage::create([
                            'merchant_id' => $merchant->user_id,
                            'image' => $this->uploadToApi($item['image']),
                            'ordering' => $item['ordering']
                        ]);
                    }
                }
            }

            DB::commit();
            $this->modal('merchant-form-modal')->close();
            $this->dispatch('notify', $this->isEdit ? 'Merchant updated successfully!' : 'Merchant created successfully!');
            $this->resetFields();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function resetFields() {
        $this->reset(['user_name','user_email','user_username','user_phone','user_password','access_code','auth_using_access_code','name','description','date_of_birth','gender','profile_picture','merchant_galleries','address','full_address','code','area_province_id','area_city_id','area_district_id','area_sub_district_id','latitude','longitude','open_time','close_time','status','point_balance','land_level','merchantId','isEdit']);
        $this->currentStep = 1;
    }

    public function create() { $this->resetFields(); $this->isEdit = false; $this->modal('merchant-form-modal')->show(); }

    public function render() {
        return view('livewire.merchant.merchant-index', [
            'merchants' => Merchant::with('user')->latest()->paginate(10)
        ]);
    }
}