# OpenMailman MCP Server

A small, self-contained [Model Context Protocol](https://modelcontextprotocol.io) server
that exposes the OpenMailman HTTP API as MCP tools over **stdio**, so other apps and AI
agents can do quick setup and automation against an OpenMailman instance.

It is a thin wrapper: every tool maps 1:1 onto a route in `routes/api.php`. It does not
touch the database, does not shell into Laravel, and has no state of its own.

---

## ⚠️ Destructive tools — read this first

Two of the seven tools cause **real, irreversible side effects**. They are flagged with
`destructiveHint: true` in their MCP annotations and say so loudly in their descriptions,
but an MCP client is free to ignore that, so treat them with care and keep tool approval
turned **on** for them.

| Tool | What it does irreversibly |
|---|---|
| `send_message` | **Sends a real message to a real recipient immediately.** The server creates the `Message` and calls `$message->send()` in the same request. There is no draft, preview, schedule or recall. |
| `list_unsubscribe_all` | **Empties a mailing list.** Runs `subscribers()->sync([])`, dropping the *entire* membership in one call. No confirmation, no partial mode, no undo. Subscriber records survive; the membership does not. |

The other five tools (`health_check`, `whoami`, `upsert_subscriber`, `diff_external_ids`,
`list_subscribe`) are read-only or additive.

If you run this server unattended, consider allow-listing only the non-destructive tools.

---

## Install & build

Requires Node 18+ (tested on Node 22).

```bash
cd mcp
npm install
npm run build        # tsc -> dist/
```

Run it directly (it speaks JSON-RPC on stdin/stdout, so there is nothing to see except the
one-line readiness banner on stderr):

```bash
OPENMAILMAN_BASE_URL="https://mailman.example.com" node dist/index.js
```

## Configuration

Everything comes from the environment. **No URL or token is ever hardcoded, and the token
is never logged** — it only goes into the `Authorization` header.

| Variable | Required | Default | Purpose |
|---|---|---|---|
| `OPENMAILMAN_BASE_URL` | **yes** | — | Base URL of the instance, e.g. `https://mailman.example.com`. Must be `http`/`https`. Trailing slashes are stripped. |
| `OPENMAILMAN_TOKEN` | no | — | Laravel Sanctum personal access token, sent as `Authorization: Bearer <token>` on **every** request. |
| `OPENMAILMAN_TIMEOUT_MS` | no | `30000` | Per-request timeout in milliseconds. |

Without `OPENMAILMAN_BASE_URL` the server exits 1 with a readable message instead of
starting half-configured.

### About the token

Today only `GET /api/user` is behind `auth:sanctum`; the five integration endpoints have
**no auth middleware**. The client nevertheless always sends the bearer token when one is
configured, so that locking those routes down (`Route::middleware('auth:sanctum')`) needs
no change here. Until then, anyone who can reach the instance can call those five
endpoints — set `OPENMAILMAN_TOKEN` now anyway so the switch is a no-op.

Issue a token from a `tinker` session (`laravel/sanctum ^3.3` is installed and `User` uses
`HasApiTokens`):

```php
php artisan tinker
>>> App\Models\User::first()->createToken('mcp')->plainTextToken;
```

---

## Wiring it up

### Claude Code (one-liner)

```bash
claude mcp add openmailman \
  --env OPENMAILMAN_BASE_URL=https://mailman.example.com \
  --env OPENMAILMAN_TOKEN=1\|your-sanctum-token \
  -- node /absolute/path/to/open-mailman/mcp/dist/index.js
```

### `claude_mcp_config` / `claude_desktop_config.json` / `.mcp.json`

```json
{
  "mcpServers": {
    "openmailman": {
      "command": "node",
      "args": ["/absolute/path/to/open-mailman/mcp/dist/index.js"],
      "env": {
        "OPENMAILMAN_BASE_URL": "https://mailman.example.com",
        "OPENMAILMAN_TOKEN": "1|your-sanctum-token"
      }
    }
  }
}
```

Use an **absolute** path to `dist/index.js`; MCP clients do not launch servers from this
directory. Build before first use — `dist/` is gitignored.

---

## Tools

| Tool | Method & route | Parameters |
|---|---|---|
| `health_check` | `GET /api/health/live` | *(none)* |
| `whoami` | `GET /api/user` | *(none)* — needs `OPENMAILMAN_TOKEN` |
| `upsert_subscriber` | `POST /api/subscribers/update-or-create` | `external_id`\* , `name`, `email`, `secondary_email`, `mobile`, `secondary_mobile`, `telegram_username`, `secondary_telegram_username`, `tags` (string array) |
| `diff_external_ids` | `POST /api/subscribers/difference-external-ids` | `external_ids`\* (array of string/number) |
| `list_subscribe` | `POST /api/list/subscribe` | `slug`\*, `code`\*, `external_id`\* |
| `list_unsubscribe_all` ⚠️ | `POST /api/list/unsubscribe-all` | `slug`\*, `code`\* |
| `send_message` ⚠️ | `POST /api/message/send` | `messenger_slug`\*, `code`\*, `to`\*, `to_name`\*, `subject`\*, `body`\* |

\* required. Field names are taken verbatim from the controllers
(`SubscriberController`, `ListController`, `MessengerController`) — none were renamed or
invented.

The routes are declared `Route::any`, so this server always uses `POST` with a JSON body
for the five integration endpoints and `GET` for the two read endpoints.

### The slug + code pair

Mailing lists and messengers are each authorised by their own `slug` plus a secret `code`
(both generated with `Str::random(10)` at creation time and visible in the OpenMailman UI).
A mismatched pair returns `404`. This is the only authorisation these endpoints currently
have, so treat codes as secrets.

### Errors

Every non-2xx response is surfaced as an MCP tool error (`isError: true`) with the status
code and a readable summary; Laravel's `422` validation payload is flattened to
`field: message | field: message`. Network failures and timeouts become readable errors
too. Nothing fails silently, and no request headers (hence never the token) appear in any
error message.

---

## Notes, inferences and known server-side quirks

These were derived by reading the controllers, not guessed. Worth knowing before you
automate against them.

1. **`upsert_subscriber` makes `external_id` required, which the API does not.**
   The server does `Subscriber::updateOrCreate(['external_id' => $request->external_id], …)`.
   If you omit `external_id` it matches on `external_id IS NULL`, so a second call with no
   `external_id` silently *overwrites* the first such subscriber. Requiring it is the one
   intentional deviation from the literal API; everything else is pass-through.

2. **`upsert_subscriber` is a full replace, not a patch.** The controller writes all nine
   columns unconditionally from the request, so any field you omit becomes `null` on the
   existing record. Always send the complete desired record.

3. **"At least one contact field" is checked twice.** The controller rejects a request
   where *none* of `email`, `secondary_email`, `mobile`, `secondary_mobile`,
   `telegram_username`, `secondary_telegram_username` is present (400). Note it uses
   `$request->has()`, so a key present with a `null` value passes the server check. This
   server applies the stricter rule — at least one must be a non-empty string — and fails
   locally with a clear message before making the call.

4. **`tags` is JSON-encoded by the server** (`json_encode($request->tags)`), so it comes
   back on the response as a JSON *string*, not an array. The tool accepts an array of
   strings, which is what the web form sends.

5. **`diff_external_ids` has a server-side bug.** It collects `['id']` (the numeric primary
   key) from the matched subscribers and diffs your `external_ids` against *those*:

   ```php
   $subscribersChunk = Subscriber::whereIn('external_id', $externalIdsChunk)->get(['id']);
   $subscribers = array_merge($subscribers, array_column($subscribersChunk->toArray(), 'id'));
   $newExternalIds = array_diff($externalIds, $subscribers);
   ```

   Unless your external ids happen to equal the primary keys, it reports every id as
   missing. The tool description says so. Fixing it means `->get(['external_id'])` +
   `array_column(…, 'external_id')` in `SubscriberController`, which is outside this
   directory's scope. `upsert_subscriber` is idempotent, so a bulk import is still safe —
   you just won't save any calls.

6. **`diff_external_ids` will 500 on a non-array `external_ids`.** The controller passes
   `$request->external_ids` straight to `array_chunk` with no validation. The tool schema
   requires a non-empty array, so a well-formed call cannot trigger it.

7. **`send_message` ignores `from`.** The `From` address and name come from the messenger
   record, not from the call. `user_id` is set from `auth()->id()`, which is `null` for an
   unauthenticated API call, and `type` is hardcoded to `'direct'`.

8. **`list_subscribe` returns `400` when the subscriber is already on the list**, which is
   surfaced as a tool error rather than treated as success — it is a real distinction, not
   a failure to be swallowed.

## Left out deliberately

* **No list/messenger/subscriber *listing* tools.** Creating a list or a messenger, and
  reading back their slug/code pairs, only exists on session-authenticated web routes, not
  in the API. Get those from the UI.
* **No `MessageController` tools** — it has no API routes.
* **No MCP resources or prompts**, no HTTP/SSE transport, no retries and no bundled test
  suite. Seven tools over stdio is the whole surface.
* **No changes to the Laravel app.** Everything lives in `mcp/`; the quirks in the Notes
  section are documented, not patched.

## Layout

```
mcp/
├── README.md
├── package.json        # pinned deps: @modelcontextprotocol/sdk 1.32.1, zod 3.25.76
├── tsconfig.json
└── src/
    ├── index.ts        # server, tool definitions, bootstrap
    └── client.ts       # config loading + HTTP client + error formatting
```
