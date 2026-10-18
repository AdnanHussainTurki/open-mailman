<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Integration &amp; API Guide
        </h2>
    </x-slot>

    @php
        $baseUrl = rtrim(url('/'), '/');
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Authentication notice --}}
            <div class="p-4 text-sm text-green-800 border border-green-300 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400 dark:border-green-800"
                role="alert">
                <h3 class="text-base font-bold uppercase">Authentication is now enforced on every integration endpoint</h3>
                <p class="mt-2">
                    <strong>All integration endpoints listed on this page require a Sanctum bearer token.</strong> The
                    integration routes in <code>routes/api.php</code> are wrapped in
                    <code>Route::middleware('auth:sanctum')-&gt;group(...)</code>, so every call must send
                    <code>Authorization: Bearer &lt;token&gt;</code>. A request with a missing, malformed or revoked
                    token is rejected with <code>401</code> and
                    <code>{"message":"Unauthenticated."}</code> &mdash; the controller is never reached.
                </p>
                <p class="mt-2">
                    <code>GET /api/health/live</code> is the one exception: it stays public, unauthenticated and
                    excluded from the throttle, so uptime monitoring needs no credentials. The per-record
                    <code>slug</code> + <code>code</code> pair is still required where documented below, but it is a
                    record selector on top of the token, <strong>not</strong> a credential in its own right.
                </p>
                <p class="mt-2">
                    This was a <strong>breaking change for any caller that was not already sending a token</strong>.
                    Issue tokens below and roll them out to your integrations.
                </p>
                <p class="mt-2">
                    Requests are rate limited to <strong>60 per minute</strong>, keyed by the authenticated user when a
                    token is present and otherwise by client IP (see <code>RouteServiceProvider</code>).
                    <code>/api/health/live</code> is excluded from the throttle.
                </p>
            </div>

            {{-- Quick start --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Quick start</h3>
                <ol class="mt-3 list-decimal list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    <li>Check the instance is up: <code>curl {{ $baseUrl }}/api/health/live</code> returns
                        <code>{"status":"alive"}</code>.</li>
                    <li>Create an API token further down this page and store it in your application's secrets. The plain
                        text value is shown once only.</li>
                    <li>Create or sync your users as <strong>subscribers</strong> via
                        <code>/api/subscribers/update-or-create</code>, keyed by your own
                        <code>external_id</code>.</li>
                    <li>In this dashboard, create a <a href="{{ route('lists') }}"
                            class="text-indigo-600 dark:text-indigo-400 underline">mailing list</a> and a <a
                            href="{{ route('messengers') }}"
                            class="text-indigo-600 dark:text-indigo-400 underline">messenger</a>. Each one shows a
                        <code>slug</code> and a <code>code</code> on its index page &mdash; the list and message
                        endpoints expect those two values in the request body, in addition to your bearer token.</li>
                    <li>Subscribe people to a list with <code>/api/list/subscribe</code>, and send one-off transactional
                        messages with <code>/api/message/send</code>.</li>
                    <li>Bulk/scheduled sending is driven by <a href="{{ route('campaigns') }}"
                            class="text-indigo-600 dark:text-indigo-400 underline">Campaigns</a> inside this dashboard;
                        there is no campaign API endpoint today.</li>
                </ol>
            </div>

            {{-- Base URL + auth --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Base URL &amp; authentication</h3>
                <dl class="mt-3 text-sm text-gray-600 dark:text-gray-400 space-y-3">
                    <div>
                        <dt class="font-semibold text-gray-900 dark:text-gray-200">Base URL</dt>
                        <dd><code>{{ $baseUrl }}/api</code></dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-900 dark:text-gray-200">Authentication</dt>
                        <dd>Laravel Sanctum personal access tokens, sent as
                            <code>Authorization: Bearer &lt;token&gt;</code>. Tokens issued here never expire
                            (<code>config/sanctum.php</code> has <code>'expiration' =&gt; null</code>) and are granted
                            the default <code>*</code> ability, so revoking is the only way to retire one.
                            <strong>Every endpoint on this page checks the token</strong>, except the public
                            <code>GET /api/health/live</code>. Unauthenticated calls to <code>api/*</code> return a
                            <code>401</code> JSON body rather than a redirect to the login page, whether or not the
                            request sends <code>Accept: application/json</code>.</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-900 dark:text-gray-200">Content type</dt>
                        <dd>Send <code>Content-Type: application/json</code> and
                            <code>Accept: application/json</code>. The <code>Accept</code> header matters: on a
                            validation failure Laravel returns a <code>422</code> JSON body only when the request
                            expects JSON; otherwise it attempts a redirect, which is useless to an API client.</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-900 dark:text-gray-200">HTTP methods</dt>
                        <dd>Every integration route is registered with <code>Route::any</code>, so GET, POST, PUT and
                            DELETE all reach the same handler. Use <code>POST</code>.</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-900 dark:text-gray-200">Empty strings</dt>
                        <dd>The global <code>ConvertEmptyStringsToNull</code> middleware turns <code>""</code> into
                            <code>null</code> before your request reaches a controller.</dd>
                    </div>
                </dl>
                <pre class="mt-4 overflow-x-auto rounded-lg bg-gray-900 p-4 text-xs text-gray-100"><code>curl -s "{{ $baseUrl }}/api/user" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $OPENMAILMAN_TOKEN"</code></pre>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Returns the token owner's user record as JSON,
                    or <code>401</code> with <code>{"message":"Unauthenticated."}</code> if the token is missing or
                    revoked. This is the easiest way to verify a token works.</p>
            </div>

            {{-- API tokens --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Your API tokens</h3>

                @if (session('plain_text_token'))
                    <div class="mt-4 p-4 text-sm text-green-800 border border-green-300 rounded-lg bg-green-50 dark:bg-gray-900 dark:text-green-400 dark:border-green-800"
                        role="alert">
                        <p class="font-bold">Token "{{ session('plain_text_token_name') }}" created. Copy it now
                            &mdash; it will never be shown again.</p>
                        <pre class="mt-2 overflow-x-auto rounded bg-gray-900 p-3 text-xs text-gray-100"><code>{{ session('plain_text_token') }}</code></pre>
                    </div>
                @endif

                @if (session('token_status'))
                    <div class="mt-4 p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-900 dark:text-blue-400"
                        role="alert">
                        {{ session('token_status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mt-4 p-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-900 dark:text-red-400"
                        role="alert">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('integration.token.store') }}"
                    class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center">
                    @csrf
                    <input type="text" name="name" required maxlength="255" placeholder="Token name (e.g. billing-app)"
                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full sm:w-80" />
                    <button type="submit"
                        class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Create token
                    </button>
                </form>

                @if ($tokens->count())
                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-6">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <td scope="col" class="px-6 py-3">Name</td>
                                    <td scope="col" class="px-6 py-3">Abilities</td>
                                    <td scope="col" class="px-6 py-3">Last used</td>
                                    <td scope="col" class="px-6 py-3">Created</td>
                                    <td scope="col" class="px-6 py-3"></td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tokens as $token)
                                    <tr
                                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <td scope="row"
                                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            {{ $token->name }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <code>{{ implode(', ', (array) $token->abilities) }}</code>
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Never' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $token->created_at ? $token->created_at->diffForHumans() : 'Unknown' }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <form method="POST"
                                                action="{{ route('integration.token.revoke', $token->id) }}"
                                                onsubmit="return confirm('Revoke this token? Any integration using it will stop working.');">
                                                @csrf
                                                <button type="submit"
                                                    class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">
                                                    Revoke
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-4 mt-6 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-900 dark:text-blue-400"
                        role="alert">
                        You have no API tokens yet.
                    </div>
                @endif
            </div>

            {{-- Endpoints --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Endpoints</h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Documented from the controller code in this instance. Fields that are not listed are ignored.
                </p>
                <div class="relative overflow-x-auto sm:rounded-lg mt-4">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <td class="px-6 py-3">Path</td>
                                <td class="px-6 py-3">Handler</td>
                                <td class="px-6 py-3">Auth today</td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="px-6 py-3"><code>GET /api/health/live</code></td>
                                <td class="px-6 py-3">closure in <code>routes/api.php</code></td>
                                <td class="px-6 py-3">None (and no throttle)</td>
                            </tr>
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="px-6 py-3"><code>GET /api/user</code></td>
                                <td class="px-6 py-3">closure in <code>routes/api.php</code></td>
                                <td class="px-6 py-3"><code>auth:sanctum</code></td>
                            </tr>
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="px-6 py-3"><code>ANY /api/subscribers/update-or-create</code></td>
                                <td class="px-6 py-3"><code>SubscriberController@updateOrCreate</code></td>
                                <td class="px-6 py-3 text-red-600 dark:text-red-400 font-semibold">None</td>
                            </tr>
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="px-6 py-3"><code>ANY /api/subscribers/difference-external-ids</code></td>
                                <td class="px-6 py-3"><code>SubscriberController@differenceExternalIds</code></td>
                                <td class="px-6 py-3 text-red-600 dark:text-red-400 font-semibold">None</td>
                            </tr>
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="px-6 py-3"><code>ANY /api/list/subscribe</code></td>
                                <td class="px-6 py-3"><code>ListController@subscribe</code></td>
                                <td class="px-6 py-3 text-red-600 dark:text-red-400 font-semibold">None</td>
                            </tr>
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="px-6 py-3"><code>ANY /api/list/unsubscribe-all</code></td>
                                <td class="px-6 py-3"><code>ListController@unsubscribeAll</code></td>
                                <td class="px-6 py-3 text-red-600 dark:text-red-400 font-semibold">None</td>
                            </tr>
                            <tr class="bg-white dark:bg-gray-800">
                                <td class="px-6 py-3"><code>ANY /api/message/send</code></td>
                                <td class="px-6 py-3"><code>MessengerController@sendMessage</code></td>
                                <td class="px-6 py-3 text-red-600 dark:text-red-400 font-semibold">None</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 1. update-or-create --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    1. Create or update a subscriber
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    <code>ANY {{ $baseUrl }}/api/subscribers/update-or-create</code> &middot;
                    <code>SubscriberController@updateOrCreate</code>
                </p>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                    Upserts a subscriber, matched on <code>external_id</code> &mdash; your own identifier for the
                    person. There are no <code>validate()</code> rules on this endpoint; the only guard is that at least
                    one of the six contact keys must be <em>present</em> in the request.
                </p>

                <h4 class="mt-4 font-semibold text-gray-900 dark:text-gray-200">Request fields</h4>
                <ul class="mt-2 list-disc list-inside text-sm text-gray-600 dark:text-gray-400 space-y-1">
                    <li><code>external_id</code> &mdash; your identifier; used as the match key for the upsert. If you
                        omit it, the match key is <code>null</code>, so you will repeatedly overwrite the same
                        <code>external_id = null</code> row. Always send it.</li>
                    <li><code>name</code></li>
                    <li><code>email</code></li>
                    <li><code>secondary_email</code></li>
                    <li><code>mobile</code></li>
                    <li><code>secondary_mobile</code></li>
                    <li><code>telegram_username</code></li>
                    <li><code>secondary_telegram_username</code></li>
                    <li><code>tags</code> &mdash; stored as <code>json_encode($request-&gt;tags)</code>. Send an array
                        of strings. If you omit it the column is written as the literal string <code>"null"</code>.</li>
                </ul>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    The "at least one field" check uses <code>$request-&gt;has()</code>, which tests for <em>key
                        presence</em>, not for a non-empty value, across these six keys: <code>email</code>,
                    <code>secondary_email</code>, <code>mobile</code>, <code>secondary_mobile</code>,
                    <code>telegram_username</code>, <code>secondary_telegram_username</code>. Sending
                    <code>"email": null</code> satisfies it. <code>name</code>, <code>external_id</code> and
                    <code>tags</code> do not satisfy it.
                </p>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Note that every listed column is written on an update, so a field you leave out is overwritten with
                    <code>null</code>. Send the subscriber's full record each time.
                </p>

                <h4 class="mt-4 font-semibold text-gray-900 dark:text-gray-200">Responses</h4>
                <ul class="mt-2 list-disc list-inside text-sm text-gray-600 dark:text-gray-400 space-y-1">
                    <li><code>200</code> &mdash; the full subscriber model serialised as JSON (its <code>id</code>,
                        <code>external_id</code>, the contact columns, <code>tags</code> as a JSON-encoded string,
                        <code>created_at</code>, <code>updated_at</code>, plus any other columns on the
                        <code>subscribers</code> table such as <code>active</code>).</li>
                    <li><code>400</code> &mdash;
                        <code>{"error":"Atleast one of the fields should have a value"}</code> when none of the six
                        contact keys is present.</li>
                </ul>

                <pre class="mt-4 overflow-x-auto rounded-lg bg-gray-900 p-4 text-xs text-gray-100"><code>curl -s -X POST "{{ $baseUrl }}/api/subscribers/update-or-create" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $OPENMAILMAN_TOKEN" \
  -d '{
    "external_id": "user-42",
    "name": "John Doe",
    "email": "john@example.com",
    "mobile": "+10000000000",
    "telegram_username": "johndoe",
    "tags": ["customers", "trial"]
  }'</code></pre>
            </div>

            {{-- 2. difference-external-ids --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    2. Diff your external IDs against this instance
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    <code>ANY {{ $baseUrl }}/api/subscribers/difference-external-ids</code> &middot;
                    <code>SubscriberController@differenceExternalIds</code>
                </p>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                    Intended to tell you which of your <code>external_id</code>s are not yet subscribers here, so you
                    can sync only the new ones.
                </p>

                <h4 class="mt-4 font-semibold text-gray-900 dark:text-gray-200">Request fields</h4>
                <ul class="mt-2 list-disc list-inside text-sm text-gray-600 dark:text-gray-400 space-y-1">
                    <li><code>external_ids</code> &mdash; array of your identifiers. Required in practice but not
                        validated: if it is missing or not an array, <code>array_chunk()</code> throws and you get a
                        <code>500</code>. It is processed in chunks of 100 internally.</li>
                </ul>

                <h4 class="mt-4 font-semibold text-gray-900 dark:text-gray-200">Responses</h4>
                <ul class="mt-2 list-disc list-inside text-sm text-gray-600 dark:text-gray-400 space-y-1">
                    <li><code>200</code> &mdash; a JSON collection of the <code>external_ids</code> you sent that were
                        not matched. Because the handler uses <code>array_diff()</code>, which preserves the original
                        array keys, the response may serialise as a JSON <em>object</em> keyed by the input position
                        (e.g. <code>{"0":"user-7","3":"user-9"}</code>) rather than a plain array. Read the values, not
                        the keys.</li>
                </ul>

                <div class="mt-4 p-4 text-sm text-yellow-800 border border-yellow-300 rounded-lg bg-yellow-50 dark:bg-gray-900 dark:text-yellow-300 dark:border-yellow-800"
                    role="alert">
                    <strong>Behaviour looks incorrect &mdash; verify before relying on it.</strong> The handler queries
                    <code>Subscriber::whereIn('external_id', $chunk)-&gt;get(['id'])</code> and then diffs your
                    <code>external_ids</code> against the returned <em>primary keys</em> (<code>id</code>), not against
                    the matched <code>external_id</code> values. Unless your external IDs happen to equal this
                    instance's row IDs, existing subscribers will still be reported as "new". We are documenting what
                    the code does rather than what the name implies; treat the result as advisory and make
                    <code>update-or-create</code> calls idempotently instead.
                </div>

                <pre class="mt-4 overflow-x-auto rounded-lg bg-gray-900 p-4 text-xs text-gray-100"><code>curl -s -X POST "{{ $baseUrl }}/api/subscribers/difference-external-ids" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $OPENMAILMAN_TOKEN" \
  -d '{"external_ids": ["user-42", "user-43", "user-44"]}'</code></pre>
            </div>

            {{-- 3. list subscribe --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    3. Subscribe someone to a mailing list
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    <code>ANY {{ $baseUrl }}/api/list/subscribe</code> &middot; <code>ListController@subscribe</code>
                </p>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                    Attaches an existing subscriber (looked up by <code>external_id</code>) to a mailing list
                    identified by its <code>slug</code> + <code>code</code> pair. Create the subscriber first with
                    endpoint 1. No <code>validate()</code> rules.
                </p>

                <h4 class="mt-4 font-semibold text-gray-900 dark:text-gray-200">Request fields</h4>
                <ul class="mt-2 list-disc list-inside text-sm text-gray-600 dark:text-gray-400 space-y-1">
                    <li><code>slug</code> &mdash; the mailing list slug, shown on the <a href="{{ route('lists') }}"
                            class="text-indigo-600 dark:text-indigo-400 underline">Lists</a> page.</li>
                    <li><code>code</code> &mdash; the mailing list's secret code, shown on the same page. The list is
                        matched on <code>slug</code> AND <code>code</code>, so a wrong code is indistinguishable from a
                        missing list.</li>
                    <li><code>external_id</code> &mdash; the subscriber's external ID.</li>
                </ul>

                <h4 class="mt-4 font-semibold text-gray-900 dark:text-gray-200">Responses</h4>
                <ul class="mt-2 list-disc list-inside text-sm text-gray-600 dark:text-gray-400 space-y-1">
                    <li><code>200</code> &mdash; <code>{"message":"Subscribed successfully"}</code></li>
                    <li><code>404</code> &mdash; <code>{"message":"Invalid mailing list"}</code> (no list matches
                        slug + code)</li>
                    <li><code>404</code> &mdash; <code>{"message":"Invalid subscriber"}</code> (no subscriber with
                        that <code>external_id</code>)</li>
                    <li><code>400</code> &mdash; <code>{"message":"Subscriber already subscribed"}</code></li>
                </ul>

                <pre class="mt-4 overflow-x-auto rounded-lg bg-gray-900 p-4 text-xs text-gray-100"><code>curl -s -X POST "{{ $baseUrl }}/api/list/subscribe" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $OPENMAILMAN_TOKEN" \
  -d '{
    "slug": "newsletter-ab12cd",
    "code": "Xy7Qa1Bz9K",
    "external_id": "user-42"
  }'</code></pre>
            </div>

            {{-- 4. unsubscribe all --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    4. Empty a mailing list
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    <code>ANY {{ $baseUrl }}/api/list/unsubscribe-all</code> &middot;
                    <code>ListController@unsubscribeAll</code>
                </p>
                <div class="mt-3 p-4 text-sm text-yellow-800 border border-yellow-300 rounded-lg bg-yellow-50 dark:bg-gray-900 dark:text-yellow-300 dark:border-yellow-800"
                    role="alert">
                    <strong>Destructive.</strong> This detaches <em>every</em> subscriber from the list
                    (<code>sync([])</code>). It is not a per-person unsubscribe, there is no confirmation step and no
                    way to undo it. There is currently no endpoint for unsubscribing a single person.
                </div>

                <h4 class="mt-4 font-semibold text-gray-900 dark:text-gray-200">Request fields</h4>
                <ul class="mt-2 list-disc list-inside text-sm text-gray-600 dark:text-gray-400 space-y-1">
                    <li><code>slug</code> &mdash; the mailing list slug.</li>
                    <li><code>code</code> &mdash; the mailing list's secret code.</li>
                </ul>

                <h4 class="mt-4 font-semibold text-gray-900 dark:text-gray-200">Responses</h4>
                <ul class="mt-2 list-disc list-inside text-sm text-gray-600 dark:text-gray-400 space-y-1">
                    <li><code>200</code> &mdash; <code>{"message":"All unsubscribed successfully"}</code></li>
                    <li><code>404</code> &mdash; <code>{"message":"Invalid mailing list"}</code></li>
                </ul>

                <pre class="mt-4 overflow-x-auto rounded-lg bg-gray-900 p-4 text-xs text-gray-100"><code>curl -s -X POST "{{ $baseUrl }}/api/list/unsubscribe-all" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $OPENMAILMAN_TOKEN" \
  -d '{
    "slug": "newsletter-ab12cd",
    "code": "Xy7Qa1Bz9K"
  }'</code></pre>
            </div>

            {{-- 5. message send --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    5. Send a one-off message
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    <code>ANY {{ $baseUrl }}/api/message/send</code> &middot;
                    <code>MessengerController@sendMessage</code>
                </p>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                    Creates a <code>direct</code> message through one of your <a href="{{ route('messengers') }}"
                        class="text-indigo-600 dark:text-indigo-400 underline">messengers</a> and attempts delivery
                    <strong>synchronously, inside the request</strong> &mdash; there is no queue, so the HTTP call
                    blocks for as long as the SMTP/Telegram conversation takes. Use a generous client timeout.
                </p>

                <h4 class="mt-4 font-semibold text-gray-900 dark:text-gray-200">Request fields (all
                    <code>required</code>)</h4>
                <ul class="mt-2 list-disc list-inside text-sm text-gray-600 dark:text-gray-400 space-y-1">
                    <li><code>messenger_slug</code> &mdash; the messenger's slug, shown on the Messenger page.</li>
                    <li><code>code</code> &mdash; the messenger's secret code, shown on the same page. Matched
                        together with the slug.</li>
                    <li><code>subject</code></li>
                    <li><code>body</code> &mdash; sent as HTML for email messengers.</li>
                    <li><code>to</code> &mdash; the destination address/number/username, depending on the messenger's
                        driver.</li>
                    <li><code>to_name</code></li>
                </ul>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    The sender is taken from the messenger record (<code>from</code>, <code>from_name</code>); you
                    cannot override it per request. The stored message is given a fresh <code>uuid</code>, type
                    <code>direct</code> and <code>scheduled_at = now()</code>. Its <code>user_id</code> comes from
                    <code>auth()-&gt;id()</code>, which is now always the owner of the bearer token used for the call,
                    since the endpoint requires authentication.
                </p>

                <h4 class="mt-4 font-semibold text-gray-900 dark:text-gray-200">Responses</h4>
                <ul class="mt-2 list-disc list-inside text-sm text-gray-600 dark:text-gray-400 space-y-1">
                    <li><code>200</code> &mdash; <code>{"success":"Message sent successfully"}</code></li>
                    <li><code>404</code> &mdash; <code>{"error":"Messenger not found"}</code> (no messenger matches
                        slug + code)</li>
                    <li><code>422</code> &mdash; the standard Laravel validation body,
                        <code>{"message":"...","errors":{"subject":["The subject field is required."]}}</code>, when a
                        required field is missing. You only get JSON here if you sent
                        <code>Accept: application/json</code>.</li>
                    <li><code>500</code> &mdash; delivery failures are not caught. If the messenger's driver is not
                        <code>email</code>, <code>sms</code> or <code>telegram</code>, or the transport throws, the
                        exception escapes and the message row is left with <code>status = "pending"</code>. A
                        <code>200</code> means "handed to the transport without throwing", not "delivered".</li>
                </ul>

                <pre class="mt-4 overflow-x-auto rounded-lg bg-gray-900 p-4 text-xs text-gray-100"><code>curl -s -X POST "{{ $baseUrl }}/api/message/send" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $OPENMAILMAN_TOKEN" \
  -d '{
    "messenger_slug": "transactional-smtp-a1b2c",
    "code": "Qw3Er5Ty7U",
    "subject": "Your receipt",
    "body": "&lt;h1&gt;Thanks!&lt;/h1&gt;&lt;p&gt;Your order shipped.&lt;/p&gt;",
    "to": "john@example.com",
    "to_name": "John Doe"
  }'</code></pre>
            </div>

            {{-- Health --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Health check</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    <code>GET {{ $baseUrl }}/api/health/live</code>
                </p>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                    Returns <code>200</code> with <code>{"status":"alive"}</code>. Excluded from the API rate limit via
                    <code>withoutMiddleware(['throttle:api'])</code>, so it is safe for an uptime monitor to poll. It
                    does not check the database or the mail transports.
                </p>
                <pre class="mt-4 overflow-x-auto rounded-lg bg-gray-900 p-4 text-xs text-gray-100"><code>curl -s "{{ $baseUrl }}/api/health/live"</code></pre>
            </div>

            {{-- MCP server --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Connect an AI agent (MCP server)</h3>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                    This project ships an <strong>MCP (Model Context Protocol)</strong> server that exposes the
                    endpoints above as tools, so an AI assistant such as Claude can manage subscribers, lists and
                    messages directly. It runs <em>locally on the operator's machine</em> and talks to this instance
                    over HTTPS &mdash; there is nothing to deploy on the server, and it needs no extra
                    permissions beyond the API token you issue above.
                </p>

                <h4 class="mt-5 text-sm font-bold uppercase text-gray-700 dark:text-gray-300">1. Build it</h4>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    From your checkout of this repository. Requires Node.js 18 or newer.
                </p>
                <pre class="mt-3 overflow-x-auto rounded-lg bg-gray-900 p-4 text-xs text-gray-100"><code>cd mcp
npm install
npm run build</code></pre>

                <h4 class="mt-5 text-sm font-bold uppercase text-gray-700 dark:text-gray-300">2. Register it</h4>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Replace <code>/path/to</code> with the absolute path to your checkout, and paste a token you
                    created above.
                </p>
                <pre class="mt-3 overflow-x-auto rounded-lg bg-gray-900 p-4 text-xs text-gray-100"><code>claude mcp add openmailman \
  --env OPENMAILMAN_BASE_URL={{ $baseUrl }} \
  --env OPENMAILMAN_TOKEN='your-token-here' \
  -- node /path/to/open-mailman/mcp/dist/index.js</code></pre>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                    Or, for any MCP client that reads a JSON config:
                </p>
                <pre class="mt-3 overflow-x-auto rounded-lg bg-gray-900 p-4 text-xs text-gray-100"><code>{
  "mcpServers": {
    "openmailman": {
      "command": "node",
      "args": ["/path/to/open-mailman/mcp/dist/index.js"],
      "env": {
        "OPENMAILMAN_BASE_URL": "{{ $baseUrl }}",
        "OPENMAILMAN_TOKEN": "your-token-here"
      }
    }
  }
}</code></pre>

                <h4 class="mt-5 text-sm font-bold uppercase text-gray-700 dark:text-gray-300">Environment variables</h4>
                <div class="mt-3 overflow-x-auto">
                    <table class="min-w-full text-sm text-left text-gray-600 dark:text-gray-400">
                        <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                            <tr>
                                <th scope="col" class="px-4 py-2">Variable</th>
                                <th scope="col" class="px-4 py-2">Required</th>
                                <th scope="col" class="px-4 py-2">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-2"><code>OPENMAILMAN_BASE_URL</code></td>
                                <td class="px-4 py-2">Yes</td>
                                <td class="px-4 py-2">This instance, e.g. <code>{{ $baseUrl }}</code>. Must be http or https.</td>
                            </tr>
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-2"><code>OPENMAILMAN_TOKEN</code></td>
                                <td class="px-4 py-2">Yes</td>
                                <td class="px-4 py-2">Sanctum token, sent as <code>Authorization: Bearer</code>. Never logged. Required for every tool except <code>health_check</code>.</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2"><code>OPENMAILMAN_TIMEOUT_MS</code></td>
                                <td class="px-4 py-2">No</td>
                                <td class="px-4 py-2">Request timeout in milliseconds. Defaults to <code>30000</code>.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h4 class="mt-5 text-sm font-bold uppercase text-gray-700 dark:text-gray-300">Available tools</h4>
                <div class="mt-3 overflow-x-auto">
                    <table class="min-w-full text-sm text-left text-gray-600 dark:text-gray-400">
                        <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                            <tr>
                                <th scope="col" class="px-4 py-2">Tool</th>
                                <th scope="col" class="px-4 py-2">Endpoint</th>
                                <th scope="col" class="px-4 py-2">Required parameters</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-2"><code>health_check</code></td>
                                <td class="px-4 py-2"><code>/api/health/live</code></td>
                                <td class="px-4 py-2">&mdash;</td>
                            </tr>
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-2"><code>whoami</code></td>
                                <td class="px-4 py-2"><code>/api/user</code></td>
                                <td class="px-4 py-2">&mdash; (token required)</td>
                            </tr>
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-2"><code>upsert_subscriber</code></td>
                                <td class="px-4 py-2"><code>/api/subscribers/update-or-create</code></td>
                                <td class="px-4 py-2"><code>external_id</code>, plus at least one contact field</td>
                            </tr>
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-2"><code>diff_external_ids</code></td>
                                <td class="px-4 py-2"><code>/api/subscribers/difference-external-ids</code></td>
                                <td class="px-4 py-2"><code>external_ids</code></td>
                            </tr>
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-2"><code>list_subscribe</code></td>
                                <td class="px-4 py-2"><code>/api/list/subscribe</code></td>
                                <td class="px-4 py-2"><code>slug</code>, <code>code</code>, <code>external_id</code></td>
                            </tr>
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-2"><code>list_unsubscribe_all</code>
                                    <span class="ml-1 inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-700/10">destructive</span>
                                </td>
                                <td class="px-4 py-2"><code>/api/list/unsubscribe-all</code></td>
                                <td class="px-4 py-2"><code>slug</code>, <code>code</code></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2"><code>send_message</code>
                                    <span class="ml-1 inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-700/10">destructive</span>
                                </td>
                                <td class="px-4 py-2"><code>/api/message/send</code></td>
                                <td class="px-4 py-2"><code>messenger_slug</code>, <code>code</code>, <code>to</code>, <code>to_name</code>, <code>subject</code>, <code>body</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 rounded-lg border-l-4 border-red-500 bg-red-50 dark:bg-red-900/20 p-4">
                    <p class="text-sm text-red-800 dark:text-red-200">
                        <strong>Two tools cause real, irreversible side effects.</strong>
                        <code>send_message</code> delivers actual email, and <code>list_unsubscribe_all</code>
                        detaches <em>every</em> subscriber from a list with no undo and no per-person alternative.
                        Both are flagged to the client as destructive, but that is only a hint &mdash; review what
                        an agent intends to do before you approve it.
                    </p>
                </div>

                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                    Full details, including known API quirks, are in <code>mcp/README.md</code> in the repository.
                </p>
            </div>

        </div>
    </div>
</x-app-layout>
