<form action="">
    <div class="sm:col-span-6">
        <label for="list"
            class="block text-sm font-medium leading-6 text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">Mailing
            Lists</label>
        <div class="mt-2">
            <select id="list" name="lists[]" autocomplete="list" multiple
                class="select2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:max-w-xs sm:text-sm sm:leading-6 dark: ring-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600 dark:focus:ring-gray-600 dark:focus:border-gray-600 dark:placeholder-gray-500 dark:focus:placeholder-gray-400 dark:placeholder-opacity-50
                ">
                @foreach (\App\Models\MailingList::all() as $list)
                    <option @if (in_array($list->id, $campaign->lists->pluck('id')->toArray())) selected @endif value="{{ $list->id }}">
                        {{ $list->name }} -
                        {{ $list->subscribers->count() }} Subscribers -
                        @if (!!$list->tags)
                            {{ implode(', ', $list->tags) }}
                        @else
                            No Tags
                        @endif
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="sm:col-span-3">
        <label for="type"
            class="block text-sm font-medium leading-6 text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">Campaign
            Type</label>
        <div class="mt-2">
            <select id="type" name="type" autocomplete="type"
                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:max-w-xs sm:text-sm sm:leading-6 dark: ring-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600 dark:focus:ring-gray-600 dark:focus:border-gray-600 dark:placeholder-gray-500 dark:focus:placeholder-gray-400 dark:placeholder-opacity-50
                                        ">
                <option @if ($campaign->type === 'STANDARD') selected @endif value="STANDARD">STANDARD</option>
                <option @if ($campaign->type === 'DYNAMIC') selected @endif value="DYNAMIC">DYNAMIC</option>
            </select>
        </div>
    </div>
    <br>
    <div class="p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400"
        role="alert">
        <ul>
            <li><b>STANDARD:</b> The activation of this campaign shall generate messages for all the subscribers even
                though they were already been added previously.</li>
            <li><b>DYNAMIC:</b> The activation of this campaign shall generate messages only for the new subscribers
                added to the linked mailing lists.
            </li>
        </ul>
    </div>
    <div class="mt-6 flex items-center justify-end gap-x-6">
        <a href="{{ route('campaign.delete', [
            'campaign' => $campaign,
        ]) }}"
            class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">Delete</a>
        <button type="button" class="text-sm font-semibold leading-6 text-gray-900 dark:text-white">Cancel</button>
        <button type="submit"
            class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Next</button>
    </div>
</form>
