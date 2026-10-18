/**
 * Minimal HTTP client for the OpenMailman API.
 *
 * Configuration comes exclusively from the environment:
 *   OPENMAILMAN_BASE_URL  (required) e.g. https://mailman.example.com
 *   OPENMAILMAN_TOKEN     (optional) Laravel Sanctum personal access token
 *
 * Nothing is hardcoded and the token is never written to stdout/stderr.
 */

export class OpenMailmanError extends Error {
  readonly status: number;
  readonly body: unknown;

  constructor(message: string, status: number, body: unknown) {
    super(message);
    this.name = "OpenMailmanError";
    this.status = status;
    this.body = body;
  }
}

export interface OpenMailmanConfig {
  baseUrl: string;
  token?: string;
  timeoutMs: number;
}

export function loadConfig(env: NodeJS.ProcessEnv = process.env): OpenMailmanConfig {
  const raw = (env.OPENMAILMAN_BASE_URL ?? "").trim();
  if (!raw) {
    throw new Error(
      "OPENMAILMAN_BASE_URL is not set. Point it at your OpenMailman instance, " +
        'e.g. OPENMAILMAN_BASE_URL="https://mailman.example.com".',
    );
  }

  let parsed: URL;
  try {
    parsed = new URL(raw);
  } catch {
    throw new Error(`OPENMAILMAN_BASE_URL is not a valid URL: ${raw}`);
  }
  if (parsed.protocol !== "http:" && parsed.protocol !== "https:") {
    throw new Error(`OPENMAILMAN_BASE_URL must be http(s), got: ${parsed.protocol}`);
  }

  const token = (env.OPENMAILMAN_TOKEN ?? "").trim();
  const timeoutRaw = Number((env.OPENMAILMAN_TIMEOUT_MS ?? "").trim());
  const timeoutMs =
    Number.isFinite(timeoutRaw) && timeoutRaw > 0 ? timeoutRaw : 30_000;

  return {
    // strip trailing slashes so joining is predictable
    baseUrl: raw.replace(/\/+$/, ""),
    token: token.length > 0 ? token : undefined,
    timeoutMs,
  };
}

export interface ApiResponse {
  status: number;
  ok: boolean;
  /** Parsed JSON when the response was JSON, otherwise undefined. */
  json?: unknown;
  /** Raw body text (always present, possibly empty). */
  text: string;
}

export class OpenMailmanClient {
  constructor(private readonly config: OpenMailmanConfig) {}

  get baseUrl(): string {
    return this.config.baseUrl;
  }

  get hasToken(): boolean {
    return this.config.token !== undefined;
  }

  async request(
    method: "GET" | "POST",
    path: string,
    body?: unknown,
  ): Promise<ApiResponse> {
    const url = `${this.config.baseUrl}${path}`;

    const headers: Record<string, string> = {
      Accept: "application/json",
    };
    if (this.config.token) {
      headers.Authorization = `Bearer ${this.config.token}`;
    }
    if (body !== undefined) {
      headers["Content-Type"] = "application/json";
    }

    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), this.config.timeoutMs);

    let res: Response;
    try {
      res = await fetch(url, {
        method,
        headers,
        body: body === undefined ? undefined : JSON.stringify(body),
        signal: controller.signal,
      });
    } catch (err) {
      const reason =
        err instanceof Error && err.name === "AbortError"
          ? `request timed out after ${this.config.timeoutMs}ms`
          : err instanceof Error
            ? err.message
            : String(err);
      // Never echo headers (and therefore never the token) in errors.
      throw new OpenMailmanError(
        `${method} ${path} could not reach ${this.config.baseUrl}: ${reason}`,
        0,
        undefined,
      );
    } finally {
      clearTimeout(timer);
    }

    const text = await res.text();
    let json: unknown;
    const contentType = res.headers.get("content-type") ?? "";
    if (contentType.includes("json") || /^\s*[[{"]/.test(text)) {
      try {
        json = JSON.parse(text);
      } catch {
        json = undefined;
      }
    }

    return { status: res.status, ok: res.ok, json, text };
  }

  /**
   * Performs the request and throws a readable OpenMailmanError on any
   * non-2xx status so that failures surface as MCP tool errors instead of
   * being silently returned as "successful" text.
   */
  async requestOrThrow(
    method: "GET" | "POST",
    path: string,
    body?: unknown,
  ): Promise<ApiResponse> {
    const res = await this.request(method, path, body);
    if (res.ok) return res;

    throw new OpenMailmanError(
      `${method} ${path} failed with HTTP ${res.status}: ${describeFailure(res)}`,
      res.status,
      res.json ?? res.text,
    );
  }
}

/** Turns a Laravel error/validation payload into a one-or-few-line summary. */
function describeFailure(res: ApiResponse): string {
  const j = res.json;
  if (j && typeof j === "object") {
    const obj = j as Record<string, unknown>;

    // Laravel 422 validation shape: { message, errors: { field: [msg, ...] } }
    if (obj.errors && typeof obj.errors === "object") {
      const parts: string[] = [];
      for (const [field, msgs] of Object.entries(obj.errors as Record<string, unknown>)) {
        const list = Array.isArray(msgs) ? msgs.map(String) : [String(msgs)];
        parts.push(`${field}: ${list.join("; ")}`);
      }
      const head = typeof obj.message === "string" ? `${obj.message} — ` : "";
      return `${head}${parts.join(" | ")}`;
    }

    for (const key of ["message", "error", "status"]) {
      if (typeof obj[key] === "string") return obj[key] as string;
    }
    return JSON.stringify(j);
  }

  const text = res.text.trim();
  if (!text) return "(empty response body)";
  return text.length > 800 ? `${text.slice(0, 800)}… (truncated)` : text;
}

/** Drops keys whose value is undefined so we don't send `"x": null` noise. */
export function compact<T extends Record<string, unknown>>(obj: T): Record<string, unknown> {
  const out: Record<string, unknown> = {};
  for (const [k, v] of Object.entries(obj)) {
    if (v !== undefined) out[k] = v;
  }
  return out;
}
