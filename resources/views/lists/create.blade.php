<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Add Mailing List
        </h2>
    </x-slot>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 text-right">
            <a href="{{ route('subscriber.create') }}"
                class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Add
                Subscriber</a>
        </div>
    </div>

    <div class="py-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if (!request()->get('subscribers'))
                        @include('lists.partials.select')
                    @else
                        <br>
                        <h2 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">Subscribers:</h2>

                        <ul class="max-w-md space-y-1 text-gray-500 list-disc list-inside dark:text-gray-400">
                            @foreach (\App\Models\Subscriber::find(request()->subscribers) as $subscriber)
                                <li>{{ $subscriber->name ?? 'Unnamed ' }} - {{ $subscriber->email ?? 'No Email ' }}</li>
                            @endforeach

                        </ul>
                        <br>


                        <form action="{{ route('list.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="subscribers" value="{{ json_encode(request()->subscribers) }}">
                            <div class="sm:col-span-3 mt-3">
                                <label for="type"
                                    class="block text-sm font-medium leading-6 text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight mt-4">Name</label>
                                <div class="mt-2">
                                    <input type="text" name="name" required placeholder="Premium Subscribers"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:max-w-xs sm:text-sm sm:leading-6 dark: ring-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600 dark:focus:ring-gray-600 dark:focus:border-gray-600 dark:placeholder-gray-500 dark:focus:placeholder-gray-400 dark:placeholder-opacity-50
                                        " />
                                </div>
                            </div>
                            <div class="sm:col-span-3 mt-3">
                                <label for="type"
                                    class="block text-sm font-medium leading-6 text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight mt-4">Tags</label>
                                <div class="mt-2">
                                    <select name="tags[]" id="tags" multiple
                                        class="select2tags block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:max-w-xs sm:text-sm sm:leading-6 dark: ring-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600 dark:focus:ring-gray-600 dark:focus:border-gray-600 dark:placeholder-gray-500 dark:focus:placeholder-gray-400 dark:placeholder-opacity-50">
                                        <option value="General">General</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-6 flex items-center justify-end gap-x-6">
                                <button type="button"
                                    class="text-sm font-semibold leading-6 text-gray-900 dark:text-white">Cancel</button>
                                <button type="submit"
                                    class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Next</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
