<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Add Campaign
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if (!request()->get('lists'))
                        @include('campaign.lists.edit_select')
                    @else
                        <br>
                        <h2 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">Mailing Lists:</h2>

                        <ul class="max-w-md space-y-1 text-gray-500 list-disc list-inside dark:text-gray-400">
                            @foreach (\App\Models\MailingList::find(request()->lists) as $list)
                                <li>{{ $list->name }} - {{ $list->subscribers->count() }} Subscribers -
                                    @if (!!$list->tags)
                                        {{ implode(', ', $list->tags) }}
                                    @else
                                        No Tags
                                    @endif
                                </li>
                            @endforeach

                        </ul>
                        <br>

                        <form id="createCampaign" action="{{ route('campaign.update', ['campaign' => $campaign]) }}"
                            method="POST">
                            @csrf
                            <input type="hidden" required name="lists" value="{{ json_encode(request()->lists) }}">
                            <input type="hidden" required name="type" value="{{ request()->type }}">
                            <div class="sm:col-span-3 mt-3">
                                <label for="type"
                                    class="block text-sm font-medium leading-6 text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight mt-4">Name</label>
                                <div class="mt-2">
                                    <input type="text" name="name" required placeholder="Premium Subscribers"
                                        value="{{ $campaign->name }}"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:max-w-xs sm:text-sm sm:leading-6 dark: ring-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600 dark:focus:ring-gray-600 dark:focus:border-gray-600 dark:placeholder-gray-500 dark:focus:placeholder-gray-400 dark:placeholder-opacity-50
                                        " />
                                </div>
                            </div>
                            <div class="sm:col-span-3 mt-3">
                                <label for="type"
                                    class="block text-sm font-medium leading-6 text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight mt-4">Subject
                                    (For Limited Messengers like Email)</label>
                                <div class="mt-2">
                                    <input type="text" name="subject" required placeholder="Hi there!"
                                        value="{{ $campaign->subject }}"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:max-w-xs sm:text-sm sm:leading-6 dark: ring-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600 dark:focus:ring-gray-600 dark:focus:border-gray-600 dark:placeholder-gray-500 dark:focus:placeholder-gray-400 dark:placeholder-opacity-50
                                        " />
                                </div>
                            </div>
                            <div class="sm:col-span-3">
                                <label for="type"
                                    class="block text-sm font-medium leading-6 text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">Messenger</label>
                                <div class="mt-2">
                                    <select id="messenger" name="messenger" autocomplete="type"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:max-w-xs sm:text-sm sm:leading-6 dark: ring-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600 dark:focus:ring-gray-600 dark:focus:border-gray-600 dark:placeholder-gray-500 dark:focus:placeholder-gray-400 dark:placeholder-opacity-50
                                        ">
                                        @foreach (\App\Models\Messenger::all() as $messenger)
                                            <option value="{{ $messenger->id }}"
                                                @if ($campaign->messenger_id == $messenger->id) selected @endif>{{ $messenger->name }}
                                                -
                                                {{ ucfirst($messenger->driver) }} -
                                                @if ($messenger->host)
                                                    {{ $messenger->host }}:{{ $messenger->port }}
                                                @else
                                                    N/A
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="sm:col-span-3 mt-3">
                                <label for="type"
                                    class="block text-sm font-medium leading-6 text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight mt-4">Rate
                                    Limiting (send one in how many seconds?)</label>
                                <div class="mt-2">
                                    <input type="number"
                                        @if ($campaign->rate_limiting_in_seconds) value="{{ $campaign->rate_limiting_in_seconds }}" @endif
                                        name="rate_limiting_seconds" required value="5"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:max-w-xs sm:text-sm sm:leading-6 dark: ring-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600 dark:focus:ring-gray-600 dark:focus:border-gray-600 dark:placeholder-gray-500 dark:focus:placeholder-gray-400 dark:placeholder-opacity-50
                                        " />
                                </div>
                            </div>
                            <div class="sm:col-span-3">
                                <label for="type"
                                    class="block text-sm font-medium leading-6 text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">Content</label>
                                <div class="mt-2">

                                    <textarea name="content" class="editor" required>{{ $campaign->content }}</textarea>


                                </div>
                            </div>
                            <div class="sm:col-span-3 mt-3">
                                <label for="type"
                                    class="block text-sm font-medium leading-6 text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight mt-4">Start
                                    From</label>
                                <div class="mt-2">
                                    <input type="datetime-local" name="scheduled_at" required
                                        value="{{ $campaign->scheduled_at->format('Y-m-d\TH:i') }}"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:max-w-xs sm:text-sm sm:leading-6 dark: ring-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600 dark:focus:ring-gray-600 dark:focus:border-gray-600 dark:placeholder-gray-500 dark:focus:placeholder-gray-400 dark:placeholder-opacity-50
                                        " />




                                </div>
                            </div>
                            <input type="hidden" name="timezone" required>

                            <script>
                                window.onload = function() {
                                    // Get user time offset from UTC
                                    var date = new Date();
                                    var offset = date.getTimezoneOffset();
                                    console.log("User's timezone offset from UTC: " + offset + " minutes")
                                    var offsetInHours = -1 * offset / 60;
                                    console.log("User's timezone offset from UTC: " + offsetInHours + " hours");

                                    // Set the value of the hidden input field
                                    document.querySelector('input[name="timezone"]').value = offsetInHours;
                                }
                            </script>
                            <div class="sm:col-span-3 mt-3">
                                <label for="type"
                                    class="block text-sm font-medium leading-6 text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight mt-4">Tags</label>
                                <div class="mt-2">
                                    <select name="tags[]" id="tags" multiple
                                        class="select2tags block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:max-w-xs sm:text-sm sm:leading-6 dark: ring-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600 dark:focus:ring-gray-600 dark:focus:border-gray-600 dark:placeholder-gray-500 dark:focus:placeholder-gray-400 dark:placeholder-opacity-50">
                                        <option @if ($campaign->tags && in_array('General', $campaign->tags)) selected @endif value="General">
                                            General</option>
                                        @foreach ($campaign->tags as $tag)
                                            @if ($tag == 'General')
                                                @continue
                                            @endif
                                            <option selected value="{{ $tag }}">{{ $tag }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mt-6 flex items-center justify-end gap-x-6">
                                <button type="button" id="sendTestMessage"
                                    class="text-sm font-semibold leading-6 text-gray-900 dark:text-white">Send Test
                                    Message</button>
                                <button type="button"
                                    class="text-sm font-semibold leading-6 text-gray-900 dark:text-white">Cancel</button>
                                <button type="submit"
                                    class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Confirm</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        {{--  #sendTestMessage will  --}}
        <script>
            $("#sendTestMessage").click(function(e) {
                e.preventDefault();
                // Validate the form by making all the inputs are filled
                var form = $("#createCampaign");
                var isValid = true;
                const emptyAllowed = ['tags[]', 'subject'];
                form.find("input, select").each(function() {
                    if (!$(this).val()) {
                        console.log($(this).attr("name") + " is empty");
                        isValid = false;
                    }
                });

                if (!isValid) {
                    alert("Please fill all the fields before sending a test message.");
                    return;
                }

                // Ask for the user's email
                var email = prompt("Enter your email address to send a test message to:");
                if (!email) {
                    return;
                }

                // Send the test message
                var data = $("#createCampaign").serialize();
                data += "&email=" + email;

                $.ajax({
                    type: "POST",
                    url: "{{ route('campaign.test') }}",
                    data: data,
                    success: function(response) {
                        alert(response.message);
                    },
                    error: function(response) {
                        alert(response.responseJSON.error);
                    }
                });

            });
        </script>
    @endpush
</x-app-layout>
