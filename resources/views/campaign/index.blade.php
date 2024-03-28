<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Campaigns
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 text-right">
            <a href="{{ route('campaign.create') }}"
                class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                Add Campaigns</a>
        </div>
    </div>

    <div class="py-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if ($campaigns->count())
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">

                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead
                                class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <td scope="col" class="px-6 py-3">
                                        Name
                                    </td>
                                    <td scope="col" class="px-6 py-3">
                                        Messenger
                                    </td>

                                    <td scope="col" class="px-6 py-3">
                                        Mailing List
                                    </td>
                                    <td>
                                        Status
                                    </td>
                                    <td scope="col" class="px-6 py-3">
                                        Tags
                                    </td>
                                    <td>
                                        Start From
                                    </td>

                                    <td>
                                        Created
                                    </td>
                                    <td scope="col" class="px-6 py-3">

                                    </td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($campaigns as $campaign)
                                    <tr
                                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <td scope="row"
                                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            {{ $campaign->name }}
                                            <br>
                                            {{ $campaign->type }}
                                            <br>
                                            <small><code>{{ $campaign->slug }}</code></small>
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $campaign->messenger->name ?? 'No Description' }}
                                            <br>


                                            <small><code>{{ $campaign->messenger->driver }}</code></small>
                                        </td>

                                        <td class="px-6 py-4">
                                            <ul
                                                class="max-w-md space-y-1 text-gray-500 campaign-disc campaign-inside dark:text-gray-400">
                                                @foreach ($campaign->lists as $list)
                                                    <li>{{ $list->name }} <br>{{ $list->subscribers->count() }}
                                                        Subscribers -
                                                        @if (!!$list->tags)
                                                            {{ implode(', ', $list->tags) }}
                                                        @else
                                                            No Tags
                                                        @endif
                                                    </li>
                                                @endforeach

                                            </ul>
                                        </td>
                                        <td>
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
                                        </td>
                                        <td class="px-6 py-4">

                                            @if (!!$campaign->tags)
                                                @foreach ($campaign->tags as $tag)
                                                    <span
                                                        class="inline-flex items-center rounded-md bg-pink-50 px-2 py-1 text-xs font-medium text-pink-700 ring-1 ring-inset ring-pink-700/10">{{ $tag }}</span>
                                                @endforeach
                                            @else
                                                No Tags
                                            @endif
                                        </td>
                                        <td>
                                            {{ $campaign->scheduled_at->diffForHumans() }}
                                            <br>
                                            <small><code>Rate limiting:
                                                    {{ $campaign->rate_limiting_in_seconds }}</code></small>
                                        </td>
                                        <td>
                                            {{ $campaign->created_at->diffForHumans() }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('campaign.show', $campaign) }}"
                                                class="inline-block rounded-md bg-indigo-600 px-2 py-1 my-1 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                                View
                                            </a>
                                            <a href="{{ route('campaign.edit', $campaign) }}"
                                                class="inline-block rounded-md bg-red-600 px-2 py-1 my-1 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                                Edit
                                            </a>

                                            <br>
                                            <a href="{{ route('campaign.history', $campaign) }}"
                                                class="inline-block  rounded-md bg-yellow-500 px-2  py-1 my-1 text-sm font-semibold text-white shadow-sm hover:bg-yellow-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                                History
                                            </a>
                                            <a href="{{ route('campaign.activate', $campaign) }}"
                                                class="inline-block  rounded-md bg-emerald-500 px-2  py-1 my-1 text-sm font-semibold text-white shadow-sm hover:bg-emerald-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                                Activate
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400"
                    role="alert">
                    There are no mailing campaigns available.
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
