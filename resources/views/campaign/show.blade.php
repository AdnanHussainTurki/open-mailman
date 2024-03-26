<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Campaign: {{ $campaign->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <dl class="max-w-md text-gray-900 divide-y divide-gray-200 dark:text-white dark:divide-gray-700">
                        <div class="flex flex-col pb-3">
                            <dt class="mb-1 text-gray-500 md:text-lg dark:text-gray-400">Campaign Name</dt>
                            <dd class="text-lg font-semibold">{{ $campaign->name }}</dd>
                        </div>

                        <!-- Repeat this pattern for each item -->
                        <div class="flex flex-col py-3">
                            <dt class="mb-1 text-gray-500 md:text-lg dark:text-gray-400">Slug</dt>
                            <dd class="text-lg font-semibold"><code>{{ $campaign->slug }}</code></dd>
                        </div>


                        <div class="flex flex-col py-3">
                            <dt class="mb-1 text-gray-500 md:text-lg dark:text-gray-400">Messenger</dt>
                            <dd class="text-lg font-semibold">{{ $campaign->messenger->name }}
                                <br><small>Driver: <code>{{ $campaign->messenger->driver }}</code></small>
                            </dd>
                        </div>

                        <div class="flex flex-col py-3">
                            <dt class="mb-1 text-gray-500 md:text-lg dark:text-gray-400">Mailing Lists</dt>
                            <dd class="text-lg font-semibold">
                                <ul class="list-disc list-inside">
                                    @foreach ($campaign->lists as $list)
                                        <li>{{ $list->name }} - {{ $list->subscribers->count() }} Subscribers</li>
                                    @endforeach
                                </ul>
                            </dd>
                        </div>

                        <div class="flex flex-col py-3">
                            <dt class="mb-1 text-gray-500 md:text-lg dark:text-gray-400">Status</dt>
                            <dd class="text-lg font-semibold">
                                @if ($campaign->status == 'draft')
                                    <span
                                        class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-700 ring-1 ring-inset ring-yellow-700/10">{{ $campaign->status }}</span>
                                @elseif ($campaign->status == 'sent')
                                    <span
                                        class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-700/10">{{ $campaign->status }}</span>
                                @else
                                    <span
                                        class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-700/10">{{ $campaign->status }}</span>
                                @endif
                            </dd>
                        </div>

                        {{-- Scheduled At --}}
                        @if ($campaign->scheduled_at)
                            <div class="flex flex-col py-3">
                                <dt class="mb-1 text-gray-500 md:text-lg dark:text-gray-400">Scheduled At</dt>
                                <dd class="text-lg font-semibold">{{ $campaign->scheduled_at }}</dd>
                            </div>
                        @endif

                        {{-- Rate limting --}}
                        @if ($campaign->rate_limiting_in_seconds)
                            <div class="flex flex-col py-3">
                                <dt class="mb-1 text-gray-500 md:text-lg dark:text-gray-400">Rate Limiting</dt>
                                <dd class="text-lg font-semibold">One message every
                                    {{ $campaign->rate_limiting_in_seconds }} seconds</dd>
                            </div>
                        @endif

                        {{-- Tags --}}
                        @if (!!$campaign->tags)
                            <div class="flex flex-col py-3">
                                <dt class="mb-1 text-gray-500 md:text-lg dark:text-gray-400">Tags</dt>
                                <dd class="text-lg font-semibold">
                                    @foreach ($campaign->tags as $tag)
                                        <span
                                            class="inline-flex items-center rounded-md bg-pink-50 px-2 py-1 text-xs font-medium text-pink-700 ring-1 ring-inset ring-pink-700/10">{{ $tag }}</span>
                                    @endforeach
                                </dd>
                            </div>
                        @endif

                        {{-- Created At --}}
                        <div class="flex flex-col py-3">
                            <dt class="mb-1 text-gray-500 md:text-lg dark:text-gray-400">Created At</dt>
                            <dd class="text-lg font-semibold">{{ $campaign->created_at }}</dd>
                        </div>

                        {{-- Content --}}
                        <div class="flex flex-col py-3">
                            <dt class="mb-1 text-gray-500 md:text-lg dark:text-gray-400">Content</dt>
                            <dd class="text-lg font-semibold">{!! $campaign->content !!}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
