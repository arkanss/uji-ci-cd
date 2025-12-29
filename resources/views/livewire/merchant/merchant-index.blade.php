<div class="p-6 max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">Merchants</flux:heading>
            <flux:subheading>Manage your business partners and locations</flux:subheading>
        </div>

        <div class="flex gap-2">
            <flux:button icon="arrow-up-tray" variant="outline" wire:click="openUploadModal">Upload CSV</flux:button>
            <flux:button icon="plus" variant="primary" wire:click="create">Add Merchant</flux:button>
        </div>
    </div>

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-50 text-red-600 rounded-lg border border-red-200">
            {{ session('error') }}
        </div>
    @endif

    <div
        class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                <tr>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Code</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Merchant Name</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Location</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Jam Operational</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($merchants as $merchant)
                    <tr wire:key="{{ $merchant->user_id }}"
                        class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                        <td class="px-4 py-3 text-sm font-mono">{{ $merchant->code }}</td>

                        <td class="px-4 py-3 text-sm font-medium">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg overflow-hidden bg-zinc-100 border border-zinc-200">
                                    @if ($merchant->profile_picture)
                                        <img src="{{ asset('storage/' . $merchant->profile_picture) }}"
                                            alt="{{ $merchant->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-zinc-400">
                                            <flux:icon icon="user" size="sm" />
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    {{ $merchant->name }}<br>
                                </div>
                            </div>
                        </td>

                        <td class="px-4 py-3 text-sm text-zinc-500">
                            {{ $merchant->address ?? '-' }}
                        </td>

                        <td class="px-4 py-3 text-sm text-zinc-500">
                            <div class="flex items-center gap-1">
                                <flux:icon icon="clock" size="xs" variant="micro" />
                                {{ $merchant->open_time ? $merchant->open_time->format('H:i') : '--:--' }} -
                                {{ $merchant->close_time ? $merchant->close_time->format('H:i') : '--:--' }}
                            </div>
                        </td>

                        <td class="px-4 py-3 text-sm">
                            @php
                                $statusColor = 'zinc';
                                if ($merchant->status == 1) {
                                    $statusColor = 'green';
                                }
                                if ($merchant->status == 0) {
                                    $statusColor = 'red';
                                }
                            @endphp

                            <flux:badge size="sm" color="{{ $statusColor }}" variant="solid">
                                {{ $merchant->status == 1 ? 'Active' : ($merchant->status == 0 ? 'Inactive' : 'Suspended') }}
                            </flux:badge>
                        </td>

                        <td class="px-4 py-3 text-right flex justify-end gap-2">
                            <flux:button size="sm" variant="ghost" icon="pencil-square"
                                wire:click="edit('{{ $merchant->user_id }}')" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-zinc-500">No merchants found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <flux:modal name="merchant-form-modal" class="md:w-[800px]">
        @if ($currentStep === 1)
            <div class="space-y-6">
                <flux:heading size="lg">{{ $isEdit ? 'Data User (Read Only)' : 'Tambah User' }}</flux:heading>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:input label="Full Name" wire:model="user_name" icon="user" :disabled="$isEdit" />
                    <flux:input label="Username" wire:model="user_username" icon="at-symbol" :disabled="$isEdit" />
                    <flux:input label="Email" type="email" wire:model="user_email" icon="envelope"
                        :disabled="$isEdit" />
                    <flux:input label="Phone Number" wire:model="user_phone" icon="phone" :disabled="$isEdit" />

                    @if (!$isEdit)
                        <div class="md:col-span-2">
                            <flux:input label="Password" type="password" wire:model="user_password" icon="key" />
                        </div>
                    @endif
                </div>

                <div class="p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <flux:checkbox label="User bisa sign in dengan kode akses" wire:model.live="auth_using_access_code"
                        :disabled="$isEdit" />
                    @if ($auth_using_access_code)
                        <div class="mt-4">
                            <flux:input label="Access Code" wire:model="access_code" :disabled="$isEdit"
                                placeholder="Masukkan kode akses unik" />
                        </div>
                    @endif
                </div>

                <div class="flex justify-end pt-4">
                    <flux:button variant="primary" wire:click="nextStep">Next: Data Merchant</flux:button>
                </div>
            </div>
        @else
            <div class="max-h-[75vh] overflow-y-auto pr-2 space-y-8">
                <section class="space-y-6">
                    <flux:heading size="md" class="border-b pb-2 text-primary-500">
                        1. Informasi Dasar
                    </flux:heading>

                    <flux:input label="Nama Merchant" wire:model="name" />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:select label="Jenis Kelamin" wire:model="gender">
                            <option value="1">Laki-laki</option>
                            <option value="2">Perempuan</option>
                        </flux:select>

                        <flux:input type="date" label="Tanggal Lahir" wire:model="date_of_birth" />
                    </div>

                    <flux:textarea label="Deskripsi" wire:model="description" rows="2" />

                    <div class="space-y-2">
                        <label class="block text-sm font-medium">
                            Foto Profil <span class="text-red-500">*</span>
                        </label>

                        <label
                            class="relative flex flex-col items-center justify-center w-full h-28 border border-dashed rounded-lg cursor-pointer bg-zinc-900/40 border-zinc-700 text-zinc-400 hover:bg-zinc-900/60 transition">
                            @if ($profile_picture && !is_string($profile_picture))
                                <img src="{{ $profile_picture->temporaryUrl() }}"
                                    class="absolute inset-0 w-full h-full object-cover rounded-lg" />
                            @elseif($isEdit && is_string($profile_picture))
                                <img src="{{ asset('storage/' . $profile_picture) }}"
                                    class="absolute inset-0 w-full h-full object-cover rounded-lg" />
                            @else
                                <span class="text-sm z-10">
                                    Drag & Drop your files or <span class="text-white font-medium">Browse</span>
                                </span>
                            @endif

                            <input type="file" wire:model="profile_picture" accept="image/*" class="hidden" />

                            <div wire:loading wire:target="profile_picture"
                                class="absolute inset-0 flex items-center justify-center bg-black/60 rounded-lg text-xs">
                                Uploading...
                            </div>
                        </label>
                    </div>

                    <div class="space-y-4">
                        <label class="block text-sm font-medium"> Foto Merchant (Gallery) </label>

                        @foreach ($merchant_galleries as $index => $gallery)
                            <div class="relative p-4 rounded-xl bg-zinc-900/40 border border-zinc-700 space-y-4">
                                <button type="button" wire:click="removeGalleryItem({{ $index }})"
                                    class="absolute top-3 right-3 text-red-500 hover:text-red-400"> 🗑 </button>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-1">
                                        <label class="text-sm font-medium"> Foto <span class="text-red-500">*</span>
                                        </label>
                                        <label
                                            class="relative flex flex-col items-center justify-center h-24 border border-dashed rounded-lg cursor-pointer bg-zinc-900/60 border-zinc-700 text-zinc-400 hover:bg-zinc-900 transition">
                                            @if (isset($gallery['image']) && !is_string($gallery['image']))
                                                <img src="{{ $gallery['image']->temporaryUrl() }}"
                                                    class="absolute inset-0 w-full h-full object-cover rounded-lg" />
                                            @elseif(isset($gallery['image']) && is_string($gallery['image']))
                                                <img src="{{ asset('storage/' . $gallery['image']) }}"
                                                    class="absolute inset-0 w-full h-full object-cover rounded-lg" />
                                            @else
                                                <span class="text-xs z-10 text-center"> Drag & Drop or <span
                                                        class="text-white font-medium">Browse</span> </span>
                                            @endif
                                            <input type="file"
                                                wire:model="merchant_galleries.{{ $index }}.image"
                                                accept="image/*" class="hidden" />
                                        </label>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-sm font-medium">Urutan</label>
                                        <flux:input type="number" min="1"
                                            wire:model="merchant_galleries.{{ $index }}.ordering" />
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="flex justify-center">
                            <flux:button size="sm" variant="outline" wire:click="addGalleryItem"> Tambah Foto
                            </flux:button>
                        </div>
                    </div>
                </section>

                <section class="space-y-4">
                    <flux:heading size="md" class="border-b pb-2 text-primary-500">2. Alamat & Lokasi
                    </flux:heading>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:input label="Kode Merchant" wire:model="code" />
                        <flux:input label="Alamat Singkat" wire:model="address" />
                        <flux:input label="Province ID" wire:model="area_province_id" />
                        <flux:input label="City ID" wire:model="area_city_id" />
                        <flux:input label="District ID" wire:model="area_district_id" />
                        <flux:input label="Sub District ID" wire:model="area_sub_district_id" />
                    </div>
                    <flux:textarea label="Alamat Lengkap" wire:model="full_address" />

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-zinc-50 dark:bg-zinc-800/30 p-4 rounded-lg">
                        <flux:input label="Latitude" wire:model.live="latitude" />
                        <flux:input label="Longitude" wire:model.live="longitude" />
                        <flux:input label="Location (Combined)" value="{{ $this->location }}" readonly
                            class="bg-zinc-900/50 text-zinc-400 cursor-not-allowed border-zinc-700" />
                    </div>
                </section>

                <section class="space-y-4">
                    <flux:heading size="md" class="border-b pb-2 text-primary-500">3. Jam Operasional
                    </flux:heading>
                    <div class="grid grid-cols-2 gap-4">
                        <flux:input type="time" label="Jam Buka" wire:model="open_time" />
                        <flux:input type="time" label="Jam Tutup" wire:model="close_time" />
                    </div>
                </section>

                <section class="space-y-4">
                    <flux:heading size="md" class="border-b pb-2 text-primary-500">4. Status & Ranking
                    </flux:heading>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <flux:select label="Status" wire:model="status">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </flux:select>
                        <flux:input type="number" label="Saldo Point" wire:model="point_balance" />
                        <flux:input type="number" label="Level Tanah" wire:model="land_level" />
                    </div>
                </section>
            </div>

            <div class="flex justify-between pt-6 mt-6 border-t">
                <flux:button variant="ghost" wire:click="previousStep">Kembali ke User</flux:button>
                <flux:button variant="primary" wire:click="save">
                    {{ $isEdit ? 'Update Data Merchant' : 'Simpan Data Merchant' }}
                </flux:button>
            </div>
        @endif
    </flux:modal>
</div>
