#!/usr/bin/env node
/**
 * OpenMailman MCP server.
 *
 * Wraps the OpenMailman HTTP API (see routes/api.php) as MCP tools, served
 * over stdio so MCP clients (Claude Code, Claude Desktop, agents, ...) can do
 * quick setup/automation against an OpenMailman instance.
 *
 * Configuration (environment only):
 *   OPENMAILMAN_BASE_URL   required, e.g. https://mailman.example.com
 *   OPENMAILMAN_TOKEN      optional Sanctum bearer token
 *   OPENMAILMAN_TIMEOUT_MS optional request timeout, default 30000
 */

import { McpServer } from "@modelcontextprotocol/sdk/server/mcp.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import type { CallToolResult } from "@modelcontextprotocol/sdk/types.js";
import { z } from "zod";

import {
  OpenMailmanClient,
  OpenMailmanError,
  compact,
  loadConfig,
  type ApiResponse,
} from "./client.js";

const SERVER_NAME = "openmailman";
const SERVER_VERSION = "0.1.0";

/** Contact fields; OpenMailman requires at least one of these to be present. */
const CONTACT_FIELDS = [
  "email",
  "secondary_email",
  "mobile",
  "secondary_mobile",
  "telegram_username",
  "secondary_telegram_username",
] as const;

// ---------------------------------------------------------------------------
// result helpers
// ---------------------------------------------------------------------------

function textResult(text: string): CallToolResult {
  return { content: [{ type: "text", text }] };
}

function errorResult(text: string): CallToolResult {
  return { isError: true, content: [{ type: "text", text }] };
}

function formatPayload(res: ApiResponse): string {
  if (res.json !== undefined) return JSON.stringify(res.json, null, 2);
  const text = res.text.trim();
  return text.length > 0 ? text : "(empty response body)";
}

function ok(summary: string, res: ApiResponse): CallToolResult {
  return textResult(`${summary} (HTTP ${res.status})\n\n${formatPayload(res)}`);
}

/** Runs a tool body, converting any throw into a readable MCP tool error. */
async function guard(fn: () => Promise<CallToolResult>): Promise<CallToolResult> {
  try {
    return await fn();
  } catch (err) {
    if (err instanceof OpenMailmanError) {
      const detail =
        err.body !== undefined && typeof err.body !== "string"
          ? `\n\n${JSON.stringify(err.body, null, 2)}`
          : "";
      return errorResult(`${err.message}${detail}`);
    }
    return errorResult(err instanceof Error ? err.message : String(err));
  }
}

// ---------------------------------------------------------------------------
// server
// ---------------------------------------------------------------------------

function buildServer(client: OpenMailmanClient): McpServer {
  const server = new McpServer(
    { name: SERVER_NAME, version: SERVER_VERSION },
    {
      instructions:
        `MCP tools for the OpenMailman instance at ${client.baseUrl}. ` +
        "Mailing-list and messenger tools are authorised by a per-record " +
        "slug + code pair, which you must obtain from the OpenMailman UI. " +
        "WARNING: send_message and list_unsubscribe_all are DESTRUCTIVE — " +
        "they send real mail and wipe list memberships, and cannot be undone.",
    },
  );

  // -- GET /api/health/live -------------------------------------------------
  server.registerTool(
    "health_check",
    {
      title: "Health check",
      description:
        "Check that the OpenMailman instance is reachable. Calls GET /api/health/live, " +
        'which returns {"status":"alive"}. Read-only; takes no arguments. Use this first ' +
        "to confirm OPENMAILMAN_BASE_URL is correct.",
      inputSchema: {},
      annotations: { readOnlyHint: true, destructiveHint: false, idempotentHint: true, openWorldHint: true },
    },
    async () =>
      guard(async () => {
        const res = await client.requestOrThrow("GET", "/api/health/live");
        return ok(`OpenMailman at ${client.baseUrl} is reachable`, res);
      }),
  );

  // -- GET /api/user --------------------------------------------------------
  server.registerTool(
    "whoami",
    {
      title: "Who am I",
      description:
        "Return the OpenMailman user that the configured bearer token belongs to. " +
        "Calls GET /api/user, which is protected by Laravel Sanctum, so this requires " +
        "OPENMAILMAN_TOKEN to be set to a valid personal access token; without one the " +
        "instance answers 401. Read-only; takes no arguments. Use it to verify your token.",
      inputSchema: {},
      annotations: { readOnlyHint: true, destructiveHint: false, idempotentHint: true, openWorldHint: true },
    },
    async () =>
      guard(async () => {
        if (!client.hasToken) {
          return errorResult(
            "No bearer token configured. GET /api/user requires auth:sanctum — " +
              "set OPENMAILMAN_TOKEN to a Sanctum personal access token and retry.",
          );
        }
        const res = await client.requestOrThrow("GET", "/api/user");
        return ok("Authenticated OpenMailman user", res);
      }),
  );

  // -- ANY /api/subscribers/update-or-create --------------------------------
  server.registerTool(
    "upsert_subscriber",
    {
      title: "Create or update a subscriber",
      description:
        "Create a subscriber, or update the existing one with the same external_id. " +
        "Calls POST /api/subscribers/update-or-create (Laravel updateOrCreate keyed on " +
        "external_id) and returns the saved subscriber record. At least one contact field " +
        "(email, secondary_email, mobile, secondary_mobile, telegram_username, " +
        "secondary_telegram_username) must be supplied. Fields you omit are written as null " +
        "on an update, so always send the full desired record, not a partial patch.",
      inputSchema: {
        external_id: z
          .string()
          .min(1)
          .describe(
            "Your own stable identifier for this subscriber. Used as the upsert key: an " +
              "existing subscriber with this external_id is updated, otherwise one is created.",
          ),
        name: z.string().optional().describe("Display name of the subscriber."),
        email: z.string().optional().describe("Primary email address."),
        secondary_email: z.string().optional().describe("Secondary email address."),
        mobile: z.string().optional().describe("Primary mobile number."),
        secondary_mobile: z.string().optional().describe("Secondary mobile number."),
        telegram_username: z.string().optional().describe("Primary Telegram username."),
        secondary_telegram_username: z
          .string()
          .optional()
          .describe("Secondary Telegram username."),
        tags: z
          .array(z.string())
          .optional()
          .describe("Free-form tags. Stored JSON-encoded on the subscriber record."),
      },
      annotations: { readOnlyHint: false, destructiveHint: false, idempotentHint: true, openWorldHint: true },
    },
    async (args) =>
      guard(async () => {
        const hasContact = CONTACT_FIELDS.some((f) => {
          const v = (args as Record<string, unknown>)[f];
          return typeof v === "string" && v.length > 0;
        });
        if (!hasContact) {
          return errorResult(
            "At least one contact field is required: " + CONTACT_FIELDS.join(", ") + ".",
          );
        }

        const res = await client.requestOrThrow(
          "POST",
          "/api/subscribers/update-or-create",
          compact({
            external_id: args.external_id,
            name: args.name,
            email: args.email,
            secondary_email: args.secondary_email,
            mobile: args.mobile,
            secondary_mobile: args.secondary_mobile,
            telegram_username: args.telegram_username,
            secondary_telegram_username: args.secondary_telegram_username,
            tags: args.tags,
          }),
        );
        return ok(`Subscriber upserted for external_id "${args.external_id}"`, res);
      }),
  );

  // -- ANY /api/subscribers/difference-external-ids -------------------------
  server.registerTool(
    "diff_external_ids",
    {
      title: "Find external IDs not yet in OpenMailman",
      description:
        "Given a list of your own external_ids, return the subset that OpenMailman does not " +
        "already have, so you can bulk-import only the missing subscribers. Calls POST " +
        "/api/subscribers/difference-external-ids and returns a JSON array of the leftover ids. " +
        "Read-only (it only queries). The instance chunks the input internally, so large lists " +
        "are fine. NOTE: the current server-side implementation diffs your external_ids against " +
        "the subscribers' numeric primary keys rather than their external_id column, so unless " +
        "your external ids happen to be those primary keys it will report every id as missing. " +
        "Treat the result as advisory and rely on upsert_subscriber (which is idempotent) for " +
        "correctness.",
      inputSchema: {
        external_ids: z
          .array(z.union([z.string(), z.number()]))
          .min(1)
          .describe("The external_ids to check against OpenMailman."),
      },
      annotations: { readOnlyHint: true, destructiveHint: false, idempotentHint: true, openWorldHint: true },
    },
    async (args) =>
      guard(async () => {
        const res = await client.requestOrThrow(
          "POST",
          "/api/subscribers/difference-external-ids",
          { external_ids: args.external_ids },
        );
        return ok(
          `Checked ${args.external_ids.length} external id(s); ids below are not present`,
          res,
        );
      }),
  );

  // -- ANY /api/list/subscribe ---------------------------------------------
  server.registerTool(
    "list_subscribe",
    {
      title: "Subscribe a subscriber to a mailing list",
      description:
        "Attach one existing subscriber to one mailing list. Calls POST /api/list/subscribe. " +
        "The list is identified by its slug plus its secret code (both shown in the OpenMailman " +
        "UI) and the subscriber by the external_id you gave it via upsert_subscriber. " +
        "Returns 404 if the slug/code pair does not match a list or the external_id is unknown, " +
        "and 400 if the subscriber is already on the list. Additive and reversible from the UI.",
      inputSchema: {
        slug: z.string().min(1).describe("Mailing list slug, e.g. my-newsletter-a1b2c3."),
        code: z
          .string()
          .min(1)
          .describe("The mailing list's secret code; must match the slug or the call 404s."),
        external_id: z
          .union([z.string(), z.number()])
          .describe("external_id of the subscriber to add to the list."),
      },
      annotations: { readOnlyHint: false, destructiveHint: false, idempotentHint: false, openWorldHint: true },
    },
    async (args) =>
      guard(async () => {
        const res = await client.requestOrThrow("POST", "/api/list/subscribe", {
          slug: args.slug,
          code: args.code,
          external_id: args.external_id,
        });
        return ok(`Subscriber "${args.external_id}" subscribed to list "${args.slug}"`, res);
      }),
  );

  // -- ANY /api/list/unsubscribe-all ---------------------------------------
  server.registerTool(
    "list_unsubscribe_all",
    {
      title: "Remove ALL subscribers from a mailing list (DESTRUCTIVE)",
      description:
        "DESTRUCTIVE AND IRREVERSIBLE: removes every subscriber from the given mailing list in " +
        "one shot. Calls POST /api/list/unsubscribe-all, which runs subscribers()->sync([]) on " +
        "the list — the whole membership is dropped, there is no confirmation step, no partial " +
        "mode and no undo, and the list cannot be repopulated except by re-subscribing each " +
        "person individually. The subscriber records themselves survive; only the membership is " +
        "destroyed. Double-check the slug and get explicit human confirmation before calling.",
      inputSchema: {
        slug: z
          .string()
          .min(1)
          .describe("Slug of the mailing list to empty. Verify this carefully — there is no undo."),
        code: z
          .string()
          .min(1)
          .describe("The mailing list's secret code; must match the slug or the call 404s."),
      },
      annotations: { readOnlyHint: false, destructiveHint: true, idempotentHint: true, openWorldHint: true },
    },
    async (args) =>
      guard(async () => {
        const res = await client.requestOrThrow("POST", "/api/list/unsubscribe-all", {
          slug: args.slug,
          code: args.code,
        });
        return ok(`All subscribers removed from list "${args.slug}"`, res);
      }),
  );

  // -- ANY /api/message/send -----------------------------------------------
  server.registerTool(
    "send_message",
    {
      title: "Send a message immediately (DESTRUCTIVE)",
      description:
        "DESTRUCTIVE AND IRREVERSIBLE: sends a real message (email/etc.) to a real recipient " +
        "right now. Calls POST /api/message/send, which creates a Message and dispatches it " +
        "immediately via the named messenger — there is no draft, preview, schedule or recall, " +
        "and a sent message cannot be unsent. The messenger is identified by its slug plus its " +
        "secret code (both from the OpenMailman UI); the From address comes from the messenger " +
        "configuration, not from this call. All six fields are required by the server. Get " +
        "explicit human confirmation of recipient and content before calling.",
      inputSchema: {
        messenger_slug: z
          .string()
          .min(1)
          .describe("Slug of the messenger (sending channel) to send through."),
        code: z
          .string()
          .min(1)
          .describe("The messenger's secret code; must match messenger_slug or the call 404s."),
        to: z.string().min(1).describe("Recipient address, e.g. an email address."),
        to_name: z.string().min(1).describe("Recipient display name. Required by the server."),
        subject: z.string().min(1).describe("Message subject line."),
        body: z
          .string()
          .min(1)
          .describe("Message body as stored and sent by the messenger (HTML is supported)."),
      },
      annotations: { readOnlyHint: false, destructiveHint: true, idempotentHint: false, openWorldHint: true },
    },
    async (args) =>
      guard(async () => {
        const res = await client.requestOrThrow("POST", "/api/message/send", {
          messenger_slug: args.messenger_slug,
          code: args.code,
          to: args.to,
          to_name: args.to_name,
          subject: args.subject,
          body: args.body,
        });
        return ok(`Message "${args.subject}" sent to ${args.to}`, res);
      }),
  );

  return server;
}

// ---------------------------------------------------------------------------
// bootstrap
// ---------------------------------------------------------------------------

async function main(): Promise<void> {
  // stdout is the MCP transport; all diagnostics must go to stderr.
  const config = loadConfig();
  const client = new OpenMailmanClient(config);
  const server = buildServer(client);

  const transport = new StdioServerTransport();
  await server.connect(transport);

  process.stderr.write(
    `${SERVER_NAME} MCP server v${SERVER_VERSION} ready on stdio ` +
      `(base URL ${config.baseUrl}, bearer token ${client.hasToken ? "configured" : "not configured"})\n`,
  );
}

main().catch((err: unknown) => {
  process.stderr.write(
    `${SERVER_NAME} MCP server failed to start: ${err instanceof Error ? err.message : String(err)}\n`,
  );
  process.exit(1);
});
