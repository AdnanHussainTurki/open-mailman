<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Messengers
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 text-right">
            <a href="{{ route('messenger.create') }}"
                class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                Add Messenger
            </a>
        </div>
    </div>

    <div class="py-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">


                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <td scope="col" class="px-6 py-3">
                                    Name
                                </td>
                                <td scope="col" class="px-6 py-3">
                                    Driver
                                </td>
                                <td scope="col" class="px-6 py-3">
                                    Description
                                </td>
                                <td scope="col" class="px-6 py-3">
                                    Created
                                </td>
                                <td scope="col" class="px-6 py-3">

                                </td>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($messengers as $messenger)
                                <tr
                                    class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <td scope="row"
                                        class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ $messenger->name }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ ucfirst($messenger->driver) }}
                                    </td>
                                    <td>
                                        <ul
                                            class="max-w-md space-y-1 text-gray-500 list-disc list-inside dark:text-gray-400">
                                            @if ($messenger->host)
                                                <li>Host: {{ $messenger->host ?? 'N/A' }}</li>
                                            @endif
                                            @if ($messenger->port)
                                                <li>Port: {{ $messenger->port ?? 'N/A' }}</li>
                                            @endif
                                            @if ($messenger->username)
                                                <li>Username: {{ $messenger->username ?? 'N/A' }}</li>
                                            @endif
                                            @if ($messenger->password)
                                                <li>Password: {{ $messenger->password ?? 'N/A' }}</li>
                                            @endif
                                            @if ($messenger->from)
                                                <li>From: {{ $messenger->from ?? 'N/A' }}</li>
                                            @endif
                                            @if ($messenger->from_name)
                                                <li>From Name: {{ $messenger->from_name ?? 'N/A' }}</li>
                                            @endif
                                            @if ($messenger->meta)
                                                <li>Meta: {{ $messenger->meta ?? 'N/A' }}</li>
                                            @endif

                                        </ul>
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $messenger->created_at->diffForHumans() }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href=""
                                            class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">View</a>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
