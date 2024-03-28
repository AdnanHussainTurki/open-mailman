<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white h-auto max-w-full rounded-lg p-4">
                    <h1 class="text-gray-900 text-7xl dark:text-white">{{ \App\Models\Campaign::count() }}</h1>
                    <h3 class="text-gray-900 uppercase text-xl font-extrabold dark:text-white">Campaigns</h3>
                </div>
                <div class="bg-white h-auto max-w-full rounded-lg p-4">
                    <h1 class="text-gray-900 text-7xl dark:text-white">{{ \App\Models\MailingList::count() }}</h1>
                    <h3 class="text-gray-900 uppercase text-xl font-extrabold dark:text-white">Mailing Lists</h3>
                </div>
                <div class="bg-white h-auto max-w-full rounded-lg p-4">
                    <h1 class="text-gray-900 text-7xl dark:text-white">{{ \App\Models\Subscriber::count() }}</h1>
                    <h3 class="text-gray-900 uppercase text-xl font-extrabold dark:text-white">Subscribers</h3>
                </div>
                <div class="bg-white h-auto max-w-full rounded-lg p-4">
                    <h1 class="text-gray-900 text-7xl dark:text-white">{{ \App\Models\Message::count() }}</h1>
                    <h3 class="text-gray-900 uppercase text-xl font-extrabold dark:text-white">Messages</h3>
                </div>
            </div>
            <div class="mt-4 bg-white h-auto max-w-full rounded-lg p-4">
                @php
                    $messageProgress =
                        100 - \App\Models\Message::where('status', 'pending')->count() / \App\Models\Message::count();
                @endphp
                <div class="flex justify-between mb-1">
                    <span
                        class="text-base font-medium  @if ($messageProgress > 80) text-emerald-600 @elseif ($messageProgress > 50) text-yellow-600 @else text-red-600 @endif dark:text-white">Messages
                        Queue</span>
                    <span
                        class="text-sm font-medium  @if ($messageProgress > 80) text-emerald-600 @elseif ($messageProgress > 50) text-yellow-600 @else text-red-600 @endif dark:text-white">{{ number_format($messageProgress, 2) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                    <div class="animate-pulse
                        @if ($messageProgress > 80) bg-emerald-600 @elseif ($messageProgress > 50) bg-yellow-600 @else bg-red-600 @endif h-2.5 rounded-full"
                        style="width:
                        {{ number_format($messageProgress, 2) }}%">
                    </div>
                </div>
            </div>
            <div class="mt-4  grid grid-cols-2 md:grid-cols-2 gap-4">
                <div class="bg-white h-auto max-w-full rounded-lg p-4">
                    <h1 class="text-yellow-400 text-7xl dark:text-yellow animate-pulse">
                        {{ \App\Models\Message::where('status', 'pending')->count() }}</h1>
                    <h3 class="text-yellow-400 uppercase text-xl font-extrabold dark:text-white">Pending Messages</h3>
                </div>
                <div class="bg-white h-auto max-w-full rounded-lg p-4">
                    <h1 class="text-red-500 text-7xl dark:text-white ">
                        {{ \App\Models\Message::where('status', 'failed')->count() }}</h1>
                    <h3 class="text-red-500 uppercase text-xl font-extrabold dark:text-white">Failed Messages</h3>
                </div>

            </div>
            <div class="mt-4 bg-white h-auto max-w-full rounded-lg p-4">
                <canvas id="myChart"></canvas>
            </div>

        </div>
    </div>
    @push('head')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endpush
    @push('scripts')
        <script>
            $(document).ready(function() {
                const ctx = document.getElementById('myChart');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($messages->pluck('date')->toArray()) !!},
                        datasets: [{
                            label: '# of Messages Sent',
                            data: {!! json_encode($messages->pluck('value')->toArray()) !!},

                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
