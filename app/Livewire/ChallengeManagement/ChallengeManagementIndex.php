<?php

namespace App\Livewire\ChallengeManagement;

use App\Models\Challenge;
use App\Models\Point;
use App\Models\Product;
use App\Enums\ChallengeTypeEnum;
use App\Enums\ChallengeTriggerTypeEnum;
use App\Enums\ChallengeStatusEnum;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use Flux\Flux;
use GuzzleHttp\Client;


#[Layout('layouts.app')]
#[Title('Challenge Management')]
class ChallengeManagementIndex extends Component
{
    use WithFileUploads;
    use WithPagination;

    public $search = '';

    public $challengeId = null;
    public $isEdit = false;

    public $name, $description, $image, $type, $trigger_type, $target;
    public $start_date, $end_date, $status;
    public $term_and_condition, $how_to_join;
    public $rewards = [];

    public function mount()
    {
        $this->resetForm();
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


    public function resetForm()
    {
        $this->reset([
            'challengeId',
            'isEdit',
            'name',
            'description',
            'image',
            'type',
            'trigger_type',
            'target',
            'start_date',
            'end_date',
            'term_and_condition',
            'how_to_join',
        ]);

        $this->status = ChallengeStatusEnum::Draft->value;
        $this->rewards = [];

        $this->addReward();
    }

    public function create()
    {
        $this->resetForm();
        Flux::modal('create-challenge')->show();
    }

    public function edit($id)
    {
        $challenge = Challenge::with('rewards')->findOrFail($id);

        $this->challengeId = $challenge->id;
        $this->isEdit = true;

        $this->name = $challenge->name;
        $this->description = $challenge->description;
        $this->type = $challenge->type->value;
        $this->trigger_type = $challenge->trigger_type->value;
        $this->target = $challenge->target;
        $this->status = $challenge->status->value;
        $this->term_and_condition = $challenge->term_and_condition;
        $this->how_to_join = $challenge->how_to_join;

        $this->start_date = $challenge->start_date?->format('Y-m-d\TH:i');
        $this->end_date = $challenge->end_date?->format('Y-m-d\TH:i');

        $this->rewards = $challenge->rewards->map(fn ($r) => [
            'reward_type' => $r->reward_type,
            'reward_amount' => $r->reward_amount,
            'point_id' => $r->point_id,
            'product_id' => $r->product_id,
        ])->toArray();

        Flux::modal('create-challenge')->show();
    }

    public function addReward()
    {
        $this->rewards[] = [
            'reward_type' => 1,
            'reward_amount' => 1,
            'point_id' => null,
            'product_id' => null,
        ];
    }

    public function removeReward($index)
    {
        unset($this->rewards[$index]);
        $this->rewards = array_values($this->rewards);
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|min:3',
            'description' => 'required',
            'image' => 'nullable|image|max:2048',
            'type' => 'required',
            'trigger_type' => 'required',
            'target' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'rewards.*.reward_amount' => 'required|integer|min:1',
            'rewards.*.reward_type' => 'required',
        ]);

        DB::transaction(function () {

            if ($this->isEdit) {
                $challenge = Challenge::findOrFail($this->challengeId);

                if ($this->image) {
                    $challenge->image = $this->uploadToApi($this->image);
                }

                $challenge->update([
                    'name' => $this->name,
                    'description' => $this->description,
                    'type' => $this->type,
                    'trigger_type' => $this->trigger_type,
                    'target' => $this->target,
                    'start_date' => $this->start_date,
                    'end_date' => $this->end_date,
                    'status' => $this->status,
                    'term_and_condition' => $this->term_and_condition,
                    'how_to_join' => $this->how_to_join,
                ]);

                $challenge->rewards()->delete();
            } else {
                $challenge = Challenge::create([
                    'name' => $this->name,
                    'description' => $this->description,
                    'image' => $this->image
                        ? $this->uploadToApi($this->image)
                        : null,
                    'type' => $this->type,
                    'trigger_type' => $this->trigger_type,
                    'target' => $this->target,
                    'start_date' => $this->start_date,
                    'end_date' => $this->end_date,
                    'status' => $this->status,
                    'term_and_condition' => $this->term_and_condition,
                    'how_to_join' => $this->how_to_join,
                ]);
            }

            foreach ($this->rewards as $reward) {
                $challenge->rewards()->create(array_filter($reward));
            }
        });

        $this->resetForm();
        Flux::modal('create-challenge')->close();
    }

    public function render()
    {
        return view('livewire.challenge-management.challenge-management-index', [
            'challenges' => Challenge::with('rewards')
                ->where('name', 'like', '%' . $this->search . '%')
                ->latest()
                ->paginate(10),

            'points' => Point::all(),
            'products' => Product::all(),
            'challengeTypes' => ChallengeTypeEnum::cases(),
            'triggerTypes' => ChallengeTriggerTypeEnum::cases(),
            'statuses' => ChallengeStatusEnum::cases(),
        ]);
    }
}
