<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Mailing Lists
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 text-right">
            <a href="{{ route('list.create') }}"
                class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                Add Mailing List</a>
        </div>
    </div>

    <div class="py-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if ($lists->count())
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
                                        Description
                                    </td>
                                    <td scope="col" class="px-6 py-3">
                                        Tags
                                    </td>
                                    <td scope="col" class="px-6 py-3">
                                        Subscribers
                                    </td>
                                    <td scope="col" class="px-6 py-3">

                                    </td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lists as $list)
                                    <tr
                                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <td scope="row"
                                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            {{ $list->name }}
                                            <br>
                                            <small><code>{{ $list->slug }}</code></small>
                                            <br>
                                            <small><code>{{ $list->code }}</code></small>
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $list->description ?? 'No Description' }}
                                        </td>
                                        <td class="px-6 py-4">

                                            @if (!!$list->tags)
                                                @foreach ($list->tags as $tag)
                                                    <span
                                                        class="inline-flex items-center rounded-md bg-pink-50 px-2 py-1 text-xs font-medium text-pink-700 ring-1 ring-inset ring-pink-700/10">{{ $tag }}</span>
                                                @endforeach
                                            @else
                                                No Tags
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <ul
                                                class="max-w-md space-y-1 text-gray-500 list-disc list-inside dark:text-gray-400">
                                                @foreach ($list->subscribers->take(10) as $subscriber)
                                                    <li>{{ $subscriber->name ?? 'Unnamed ' }} -
                                                        {{ $subscriber->email ?? 'No Email ' }}</li>
                                                @endforeach
                                                <li>Total: {{ $list->subscribers->count() }} subscribers</li>

                                            </ul>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('list.show', $list) }}"
                                                class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">View</a>
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
                    There are no mailing lists available.
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
