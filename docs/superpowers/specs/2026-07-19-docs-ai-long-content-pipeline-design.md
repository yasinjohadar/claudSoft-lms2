# Documentation AI Long-Content Pipeline — Design

**Date:** 2026-07-19  
**Status:** Approved (approach B)  
**Scope:** Admin docs AI — create, refine (improve), enhance

## Problem

Single synchronous HTTP calls for medium/long documentation generation time out. The browser shows «فشل الاتصال بالخادم» because the request dies before a valid JSON response.

## Goals

1. Produce one cohesive long HTML documentation page (user-facing result stays a single page).
2. Survive long generation without connection drop.
3. Live progress if the admin stays on the page; continue in the background if they leave.
4. Cover create + refine + enhance with the same job/progress infrastructure.

## Non-goals

- Multi-page tree auto-generation (future).
- Streaming token-by-token UI (optional later).
- Changing TinyMCE save flow.

## Architecture

```
Admin UI  --POST start-->  Controller  --> DocumentationAiGeneration (queued/pending)
                |                              |
                +--poll GET status <--+         v
                                      ProcessDocumentationAiJob (queue or afterResponse)
                                               |
                                               v
                                    DocumentationAiPipelineService
                                      1) outline / plan chunks
                                      2) generate or transform each chunk
                                      3) assemble (+ light coherence)
                                      4) meta (create only, optional)
                                               |
                                               v
                                    status=completed, result JSON
```

### Persistence

Table `documentation_ai_generations`:

| Column | Purpose |
|--------|---------|
| `uuid` | Public poll id |
| `user_id` | Owner |
| `operation` | `generate` \| `refine` \| `enhance` |
| `status` | `queued` \| `running` \| `completed` \| `failed` \| `cancelled` |
| `progress` | 0–100 |
| `stage` | Human-readable stage key |
| `stage_label` | Arabic label for UI |
| `payload` | Input options (topic, length, notes, engine, model ids, source_html ref…) |
| `partial_result` | Accumulating sections / draft |
| `result` | Final wizard-shaped JSON |
| `error_message` | Failure text |
| `timestamps` | |

Large `source_html` for refine/enhance stored in payload or `storage` file path if > DB comfort; prefer compressed text in JSON for v1 with existing max char limits, chunked processing.

### Pipeline — generate

1. **outline** — structured: title, slug, excerpt, sections[{heading, brief}]
2. **sections** — for each section: HTML `content-section` block with style guide; prompt includes full outline + prior headings for coherence
3. **assemble** — join sections; ensure title/slug/excerpt
4. **meta** (optional) — short second call or derive from excerpt

Target lengths map to outline section counts (short ~3–4, medium ~6–8, long ~10–14) so each AI call stays within timeout/token limits while the assembled page is long.

### Pipeline — refine / enhance

1. **split** — chunk by `section.content-section` or `h2`; fallback by size windows
2. **transform** — each chunk with user notes + tone/language (enhance requires notes)
3. **assemble** — stitch HTML; optional short global polish for transitions only
4. Return `{ content, excerpt? }` (+ stats for enhance)

### Queue strategy

- Job `ProcessDocumentationAiJob` (`ShouldQueue`, high `$timeout`, few `$tries`).
- If `queue.default === sync`, dispatch via `afterResponse()` so HTTP returns immediately on local `artisan serve`.
- Otherwise push to database/redis queue (worker required for leave-and-return).

### API

- `POST admin/docs/ai-pages/jobs` — start (body: operation + fields)
- `GET admin/docs/ai-pages/jobs/{uuid}` — status/progress/result
- Keep legacy `generate`/`refine` endpoints as thin wrappers that start a job + optionally wait is **not** used; UI switches to async only.

### UI

- On submit: start job, show progress bar + stage label, poll every 2s.
- On `completed`: fill form fields / TinyMCE like today.
- Persist last `job uuid` in `sessionStorage` keyed by page; on load, if still running, resume polling.
- Clearer errors from `error_message` (never generic «فشل الاتصال» when we have job failure).

## Success criteria

- Medium/long generate completes without browser connection error.
- Final content is one coherent HTML page meeting length targets better than a single truncated call.
- Leaving the create page and returning still shows progress / result when a queue worker (or afterResponse) finished the job.
- Refine and enhance use the same progress UX for long sources.

## Risks

- Coherence across sections: mitigated by outline + prior-headings context.
- Queue not running in prod: document `queue:work`; status stays `queued` with UI hint.
- Token limits per section: keep section prompts bounded; increase section count not section size for «long».
