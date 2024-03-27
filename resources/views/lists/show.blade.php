<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $list->name }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 text-right">
            <a href="{{ route('list.create') }}"
                class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                ← Back to Mailing List</a>
        </div>
    </div>

    <div class="py-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                @if ($subscribers->count())
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
                                            Email
                                        </td>
                                        <td scope="col" class="px-6 py-3">
                                            Phone
                                        </td>
                                        <td scope="col" class="px-6 py-3">
                                            Telegram
                                        </td>
                                        <td scope="col" class="px-6 py-3">
                                            External ID
                                        </td>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($subscribers as $subscriber)
                                        <tr
                                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                            <td scope="row"
                                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                {{ $subscriber->name }}

                                            </td>
                                            <td class="px-6 py-4">
                                                {{ $list->email ?? 'No Primary Email' }} <br>
                                                {{ $list->secondary_email ?? 'No Secondary Email' }}
                                            </td>
                                            <td class="px-6 py-4">

                                                {{ $list->mobile ?? 'No Primary Mobile' }} <br>
                                                {{ $list->secondary_mobile ?? 'No Secondary Mobile' }}
                                            </td>
                                            <td class="px-6 py-4">
                                                {{ $list->telegram_username ?? 'No Primary Telegram' }} <br>
                                                {{ $list->secondary_telegram_username ?? 'No Secondary Telegram' }}
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                {{ $list->external_id ?? 'No External ID' }}
                                            </td>
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>
                        {{ $subscribers->links() }}
                    </div>
                @else
                    <div class="p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400"
                        role="alert">
                        There are no mailing lists available.
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
