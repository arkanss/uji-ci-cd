@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="p-6 max-w-7xl mx-auto">
    <h1 class="text-2xl font-bold">Dashboard</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-lg shadow flex items-center justify-between">
            <div>
                <span class="text-sm text-zinc-500 dark:text-zinc-400">
                    Total Pesanan
                </span>
                <div class="text-2xl font-bold">12</div>
            </div>
            <div class="p-3 rounded-full bg-zinc-100 dark:bg-zinc-800">
                <flux:icon.shopping-cart variant="outline" class="w-6 h-6 text-zinc-600 dark:text-zinc-300" />
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-4 rounded-lg shadow flex items-center justify-between">
            <div>
                <span class="text-sm text-zinc-500 dark:text-zinc-400">
                    Total Produk
                </span>
                <div class="text-2xl font-bold">5</div>
            </div>
            <div class="p-3 rounded-full bg-zinc-100 dark:bg-zinc-800">
                <flux:icon.cube variant="outline" class="w-6 h-6 text-zinc-600 dark:text-zinc-300" />
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-4 rounded-lg shadow flex items-center justify-between">
            <div>
                <span class="text-sm text-zinc-500 dark:text-zinc-400">
                    Completed Orders
                </span>
                <div class="text-2xl font-bold">3</div>
            </div>

            <div class="p-3 rounded-full bg-zinc-100 dark:bg-zinc-800">
                <flux:icon.check-circle variant="outline" class="w-6 h-6 text-zinc-600 dark:text-zinc-300" />
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-lg shadow">
            <h2 class="font-semibold mb-2">Bar Chart</h2>
            <div class="h-80">
                <canvas id="barChart" class="w-full h-full"></canvas>
            </div>
        </div>
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-lg shadow">
            <h2 class="font-semibold mb-2">Line Chart</h2>
            <div class="h-80">
                <canvas id="lineChart" class="w-full h-full"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const barCtx = document.getElementById('barChart');
        if (barCtx) {
            new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Orders',
                        data: [10, 25, 18, 32, 20, 12],
                        backgroundColor: 'rgba(34,197,94,0.6)',
                        borderColor: 'rgba(34,197,94,1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        const lineCtx = document.getElementById('lineChart');
        if (lineCtx) {
            new Chart(lineCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                            label: 'Sales 2024',
                            data: [4, 10, 8, 12, 15, 9],
                            borderColor: 'rgba(34,197,94,1)',
                            backgroundColor: 'rgba(34,197,94,0.2)',
                            tension: 0.3
                        },
                        {
                            label: 'Sales 2023',
                            data: [3, 7, 6, 9, 11, 4],
                            borderColor: 'rgba(16,185,129,1)',
                            backgroundColor: 'rgba(16,185,129,0.2)',
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

    });
</script>
@endsection