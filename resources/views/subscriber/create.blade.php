<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Add Subscriber
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form id="createSubscriber" action="{{ route('subscriber.store') }}" method="POST">
                        @csrf
                        <div class="sm:col-span-3 mt-3">
                            <label for="type"
                                class="block text-sm font-medium leading-6 text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight mt-4">Name</label>
                            <div class="mt-2">
                                <input type="text" name="name" placeholder="John Doe"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:max-w-xs sm:text-sm sm:leading-6 dark: ring-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600 dark:focus:ring-gray-600 dark:focus:border-gray-600 dark:placeholder-gray-500 dark:focus:placeholder-gray-400 dark:placeholder-opacity-50
                                        " />
                            </div>
                        </div>
                        <div class="sm:col-span-3 mt-3">
                            <label for="type"
                                class="block text-sm font-medium leading-6 text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight mt-4">Email</label>
                            <div class="mt-2">
                                <input type="email" name="email" placeholder="you@somewhere.org"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:max-w-xs sm:text-sm sm:leading-6 dark: ring-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600 dark:focus:ring-gray-600 dark:focus:border-gray-600 dark:placeholder-gray-500 dark:focus:placeholder-gray-400 dark:placeholder-opacity-50
                                        " />
                            </div>
                        </div>
                        {{-- Secondary email --}}
                        <div class="sm:col-span-3 mt-3">
                            <label for="type"
                                class="block text-sm font-medium leading-6 text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight mt-4">Secondary
                                Email</label>
                            <div class="mt-2">
                                <input type="email" name="secondary_email" placeholder="you@somewhereelse.org"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:max-w-xs sm:text-sm sm:leading-6 dark: ring-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600 dark:focus:ring-gray-600 dark:focus:border-gray-600 dark:placeholder-gray-500 dark:focus:placeholder-gray-400 dark:placeholder-opacity-50
                                        " />
                            </div>
                        </div>
                        {{-- Primary Mobile --}}
                        <div class="sm:col-span-3 mt-3">
                            <label for="type"
                                class="block text-sm font-medium leading-6 text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight mt-4">Primary
                                Mobile</label>
                            <div class="mt-2">
                                <input type="tel" name="mobile" placeholder="+1234567890"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:max-w-xs sm:text-sm sm:leading-6 dark: ring-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600 dark:focus:ring-gray-600 dark:focus:border-gray-600 dark:placeholder-gray-500 dark:focus:placeholder-gray-400 dark:placeholder-opacity-50
                                        " />
                            </div>
                        </div>
                        {{-- Secondary Mobile --}}
                        <div class="sm:col-span-3 mt-3">
                            <label for="type"
                                class="block text-sm font-medium leading-6 text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight mt-4">Secondary
                                Mobile</label>
                            <div class="mt-2">
                                <input type="tel" name="secondary_mobile" placeholder="+1234567890"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:max-w-xs sm:text-sm sm:leading-6 dark: ring-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600 dark:focus:ring-gray-600 dark:focus:border-gray-600 dark:placeholder-gray-500 dark:focus:placeholder-gray-400 dark:placeholder-opacity-50
                                        " />
                            </div>
                        </div>
                        {{-- Primary Telegram --}}
                        <div class="sm:col-span-3 mt-3">
                            <label for="type"
                                class="block text-sm font-medium leading-6 text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight mt-4">Primary
                                Telegram</label>
                            <div class="mt-2">
                                <input type="text" name="telegram_username" placeholder="username"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:max-w-xs sm:text-sm sm:leading-6 dark: ring-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600 dark:focus:ring-gray-600 dark:focus:border-gray-600 dark:placeholder-gray-500 dark:focus:placeholder-gray-400 dark:placeholder-opacity-50
                                        " />
                            </div>
                        </div>
                        {{-- Secondary Telegram --}}
                        <div class="sm:col-span-3 mt-3">
                            <label for="type"
                                class="block text-sm font-medium leading-6 text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight mt-4">Secondary
                                Telegram</label>
                            <div class="mt-2">
                                <input type="text" name="secondary_telegram_username" placeholder="username2"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:max-w-xs sm:text-sm sm:leading-6 dark: ring-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600 dark:focus:ring-gray-600 dark:focus:border-gray-600 dark:placeholder-gray-500 dark:focus:placeholder-gray-400 dark:placeholder-opacity-50
                                        " />
                            </div>
                        </div>
                        {{-- Tags --}}
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
                            <button type="reset"
                                class="text-sm font-semibold leading-6 text-gray-900 dark:text-white">Cancel</button>
                            <button type="submit"
                                class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Submit</button>
                        </div>

                        <script>
                            document.querySelector('#createSubscriber').addEventListener('submit', function(e) {
                                let inputs = Array.from(this.querySelectorAll('input.block'));
                                // Filter inputs where attribute "name" != name
                                inputs = inputs.filter(input => input.getAttribute('name') !== 'name');

                                if (!inputs.some(input => input.value.trim() !== '')) {
                                    alert('At least one field must be filled out');
                                    e.preventDefault();

                                }
                            });
                        </script>


                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
