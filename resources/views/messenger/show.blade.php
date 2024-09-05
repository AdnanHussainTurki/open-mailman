<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Messages : {{ $messenger->name }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 text-right">
            <a href="{{ route('messengers') }}"
                class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                ← Back to Messengers</a>
        </div>
    </div>

    <div class="py-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="p-6 text-gray-900 dark:text-gray-100">

                @if ($messages->count())
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">

                        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                <thead
                                    class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <td scope="col" class="px-6 py-3">
                                            From
                                        </td>
                                        <td scope="col" class="px-6 py-3">
                                            To
                                        </td>
                                        <td scope="col" class="px-6 py-3">
                                            Content
                                        </td>
                                        <td scope="col" class="px-6 py-3">
                                            Scheduled for
                                        </td>
                                        <td>
                                            Status
                                        </td>

                                        <td>
                                            Created
                                        </td>
                                        <td>
                                            Mark as
                                        </td>
                                        <td>
                                            Action
                                        </td>

                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($messages as $message)
                                        <tr
                                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                            <td scope="row"
                                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                {{ $message->from_name ?? 'N/A' }}
                                                <br>
                                                <small><code>{{ $message->from ?? 'N/A' }}</code></small>
                                            </td>
                                            <td class="px-6 py-4">
                                                {{ $message->to_name }}
                                                <br>
                                                <small><code>{{ $message->to }}</code></small>
                                                <br>
                                                <small><code>Send via {{ $message->messenger->driver }}</code></small>
                                            </td>

                                            <td class="px-6 py-4">
                                                Subject: {{ substr($message->subject, 0, 100) ?? 'No Subject' }}
                                                <br>
                                                Body:
                                                {!! substr($message->body, 0, 100) ?? 'No Body' !!}
                                            </td>

                                            <td class="px-6 py-4">
                                                {{ $message->scheduled_at->format('Y-m-d H:i:s') }}

                                            </td>
                                            <td>
                                                @switch($message->status)
                                                    @case('sent')
                                                        <span
                                                            class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-yellow-700/10">Sent</span>
                                                    @break

                                                    @case('failed')
                                                        <span
                                                            class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-yellow-700/10">Failed</span>
                                                    @break

                                                    @case('pending')
                                                        <span
                                                            class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-700 ring-1 ring-inset ring-yellow-700/10">Pending</span>
                                                    @break

                                                    @default
                                                        <span
                                                            class="inline-flex items-center rounded-md bg-grey-50 px-2 py-1 text-xs font-medium text-grey-700 ring-1 ring-inset ring-yellow-700/10">Unknown</span>
                                                @endswitch
                                            </td>
                                            <td class="px-6 py-4">
                                                <ul>
                                                    {{-- print try_count, max_try_count, sent_at, failed_at, delivered_at, opened_at, clicked_at --}}
                                                    @if ($message->try_count)
                                                        <li>Try Count: {{ $message->try_count }}</li>
                                                    @endif
                                                    @if ($message->max_try_count)
                                                        <li>Max Try Count: {{ $message->max_try_count }}</li>
                                                    @endif
                                                    @if ($message->sent_at)
                                                        <li>Sent At: {{ $message->sent_at }}</li>
                                                    @endif
                                                    @if ($message->failed_at)
                                                        <li>Failed At: {{ $message->failed_at }}</li>
                                                    @endif
                                                    @if ($message->delivered_at)
                                                        <li>Delivered At: {{ $message->delivered_at }}</li>
                                                    @endif
                                                    @if ($message->opened_at)
                                                        <li>Opened At: {{ $message->opened_at }}</li>
                                                    @endif
                                                    @if ($message->clicked_at)
                                                        <li>Clicked At: {{ $message->clicked_at }}</li>
                                                    @endif

                                                </ul>
                                            </td>

                                            <td>
                                                {{ $message->created_at->diffForHumans() }}
                                            </td>
                                            <td>
                                                @if ($message->status != 'sent')
                                                    <a href="{{ route('message.change-status', ['message' => $message, 'status' => 'sent']) }}"
                                                        class="inline-block  rounded-md bg-emerald-500 px-2  py-1 my-1 text-sm font-semibold text-white shadow-sm hover:bg-emerald-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                                        Sent
                                                    </a>
                                                @endif
                                                @if ($message->status != 'pending')
                                                    <br>
                                                    <a href="{{ route('message.change-status', ['message' => $message, 'status' => 'pending']) }}"
                                                        class="inline-block  rounded-md bg-yellow-500 px-2  py-1 my-1 text-sm font-semibold text-white shadow-sm hover:bg-yellow-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                                        Pending
                                                    </a>
                                                @endif
                                                @if ($message->status != 'failed')
                                                    <br>
                                                    <a href="{{ route('message.change-status', ['message' => $message, 'status' => 'failed']) }}"
                                                        class="inline-block  rounded-md bg-red-500 px-2  py-1 my-1 text-sm font-semibold text-white shadow-sm hover:bg-red-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                                        Failed
                                                    </a>
                                                @endif

                                            </td>
                                            <td>
                                                @if ($message->status == 'pending')
                                                    <br>
                                                    <a href="{{ route('message.send', ['message' => $message]) }}"
                                                        class="inline-block  rounded-md bg-yellow-500 px-2  py-1 my-1 text-sm font-semibold text-white shadow-sm hover:bg-yellow-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                                        Send
                                                    </a>
                                                @endif
                                                @if ($message->status == 'failed')
                                                    <br>
                                                    <a href="{{ route('message.send', ['message' => $message]) }}"
                                                        class="inline-block  rounded-md bg-red-500 px-2  py-1 my-1 text-sm font-semibold text-white shadow-sm hover:bg-red-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                                        Resend
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                            <div class="m-3 p-3 px-6 py-4">
                                {{ $messages->links() }}

                            </div>
                        </div>
                    </div>
                @else
                    <div class="p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400"
                        role="alert">
                        There are no messages available for this messenger.
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
