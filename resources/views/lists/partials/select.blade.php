<form action="">
    <div class="sm:col-span-3">
        <label for="subscriber"
            class="block text-sm font-medium leading-6 text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">Subscribers</label>
        <div class="mt-2">
            <select id="subscriber" name="subscribers[]" multiple autocomplete="subscriber"
                class="select2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:max-w-xs sm:text-sm sm:leading-6 dark: ring-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600 dark:focus:ring-gray-600 dark:focus:border-gray-600 dark:placeholder-gray-500 dark:focus:placeholder-gray-400 dark:placeholder-opacity-50
                ">
                @foreach (\App\Models\Subscriber::get() as $subscriber)
                    <option value="{{ $subscriber->id }}">{{ $subscriber->name ?? 'Unnamed ' }} -
                        {{ $subscriber->email ?? 'No Email ' }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class=" flex items-center justify-start gap-x-6">
        <a href="{{ route('subscriber.create') }}"
            class="text-sm font-semibold mt-2 leading-6 text-gray-900 dark:text-white">Add
            Subcriber</button>

    </div>
    <div class="mt-6 flex items-center justify-end gap-x-6">
        <button type="button" class="text-sm font-semibold leading-6 text-gray-900 dark:text-white">Cancel</button>
        <button type="submit"
            class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Next</button>
    </div>
</form>
