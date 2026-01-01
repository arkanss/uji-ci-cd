<div class="p-6 max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">DP Item Requests</flux:heading>
            <flux:subheading>Scan QR Code to open or create request</flux:subheading>
        </div>

        <flux:button variant="primary" icon="qr-code" x-data x-on:click="$dispatch('open-qr')">
            Scan QR Code
        </flux:button>
    </div>

    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                <tr>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Code</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Requested By</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Created At</th>
                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($requests as $request)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <td class="px-4 py-3 font-mono text-sm">{{ $request->code }}</td>
                        <td class="px-4 py-3">
                            <flux:badge :color="$request->status->color()">
                                {{ $request->status->label() }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $request->user?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-zinc-500">{{ $request->created_at->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3 text-right">
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                    inset="top bottom" />

                                <flux:menu>
                                    <flux:menu.item icon="eye"
                                        href="{{ route('dp-item-requests.show', $request->id) }}">
                                        Detail
                                    </flux:menu.item>

                                    @if (in_array($request->status->value, [1, 2]))
                                        <flux:menu.separator />
                                        <flux:menu.item icon="pencil-square"
                                            href="{{ route('dp-item-requests.edit', $request->id) }}">
                                            Edit
                                        </flux:menu.item>
                                    @endif
                                </flux:menu>
                            </flux:dropdown>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-zinc-500">No DP Item Requests found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $requests->links() }}</div>

    {{-- QR MODAL --}}
    <div x-data="qrModalZXing()" x-on:open-qr.window="open()" x-on:keydown.escape.window="close()" x-show="openModal"
        x-transition.opacity x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white dark:bg-zinc-900 rounded-xl p-4" x-transition.scale
            style="width: fit-content; max-width: 95%;">
            <div class="space-y-4">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-lg font-semibold">Scan QR Code</h2>
                        <p class="text-sm text-zinc-500">Arahkan kamera ke QR Code</p>
                    </div>
                    <button class="text-zinc-400 hover:text-zinc-600" x-on:click="close()">✕</button>
                </div>

                <video id="scannerVideo" autoplay muted playsinline
                    class="relative mx-auto rounded-lg w-80 h-60 bg-black border border-zinc-200 dark:border-zinc-800"></video>

                <div class="flex justify-end">
                    <button class="px-4 py-2 text-sm rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200"
                        x-on:click="close()">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function qrModalZXing() {
            return {
                openModal: false,
                codeReader: null,
                selectedDevice: null,
                devices: [],

                async open() {
                    this.openModal = true
                    this.$nextTick(() => this.startCamera())
                },

                async startCamera() {
                    if (!window.ZXing) {
                        await new Promise(res => {
                            const s = document.createElement('script')
                            s.src = 'https://unpkg.com/@zxing/library@latest'
                            s.onload = res
                            document.head.appendChild(s)
                        })
                    }

                    const list = await navigator.mediaDevices.enumerateDevices()
                    this.devices = list.filter(d => d.kind === 'videoinput')
                    this.selectedDevice ??= this.devices[0]?.deviceId

                    this.codeReader = new ZXing.BrowserMultiFormatReader()
                    this.codeReader.decodeFromVideoDevice(
                        this.selectedDevice,
                        'scannerVideo',
                        (result) => {
                            if (result?.text) {
                                this.codeReader.reset()
                                this.close()

                                const parts = result.text.split(':')
                                const uuid = parts.at(-1)

                                window.location.href = `/dp-item-requests/${uuid}`
                            }
                        }
                    )
                },

                close() {
                    if (this.codeReader) {
                        this.codeReader.reset()
                        this.codeReader = null
                    }
                    this.openModal = false
                }
            }
        }
    </script>
</div>
