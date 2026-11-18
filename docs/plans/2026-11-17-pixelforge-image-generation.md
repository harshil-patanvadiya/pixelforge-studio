# Pixelforge image generation: implementation plan

Status: draft, awaiting confirmation.

## Objective

Signed in users generate images through Pixelforge, see what each generation will cost before submitting, and see live status, estimated cost, actual cost and the image in their history. Every succeeded generation is charged exactly once at Pixelforge's cost plus 25%. Failed generations cost nothing. Operators get an admin dashboard whose provider costs match Pixelforge's usage report.

## Scope

In scope now:

1. User authentication (already working from the starter kit, only an admin flag is added).
2. Image generation.
3. Cost calculation (estimate before, actual after).
4. Generation history with live status.
5. Admin dashboard, including reconciliation against Pixelforge usage.

Out of scope for now: credit balances and wallet, buying credits, storing images ourselves, deployment, README, automated tests. Tests are left out because the project guidelines say not to write them unless asked. Given that the assessment grades money correctness first, I recommend adding pricing and webhook tests later; say the word and they go into this plan.

## Corrections to the feature list

* "Cost cutting" is read as cost calculation. Please correct me if you meant something else.
* Pixelforge sends no "queued" webhook. The events are processing, succeeded, failed and ping. The queued status comes from the create response.
* "Admin can add credits" belongs to the wallet module, which is out of scope.

## Decisions (please confirm or change)

1. **Money unit.** All amounts are stored as whole nanodollars (one billionth of a dollar). This matches the nine decimal places Pixelforge uses, so provider costs are exact. Rates are parsed from their decimal strings without floating point. If Pixelforge ever publishes a rate too precise to fit, the app fails loudly instead of rounding.
2. **Markup rounding.** Customer price is provider cost times 1.25, rounded up to the next whole nanodollar. The rounding is under one nanodollar per generation and never undercharges.
3. **Estimate shown up front.** Two numbers: a typical price (midpoint of the reasoning range, expected text tokens) and a maximum price (most text and reasoning tokens possible). The actual charge can never exceed the maximum. Both are stored on the generation.
4. **Which rates.** Estimates use the current published pricing (fetched from Pixelforge and cached briefly). Actual cost uses the rates returned on that generation, which are fixed at creation.
5. **Live status.** The history page refreshes itself every few seconds while any generation is unfinished. No websockets.
6. **Polling fallback.** A scheduled check every minute asks Pixelforge about generations unfinished for longer than a few minutes, in case webhooks were lost.
7. **Making an admin.** A one off console action marks a user as admin. There is no admin management UI.
8. **Reporting day.** Admin figures are grouped by the UTC day a generation completed, because that is how Pixelforge bills and reports usage.

## Data

**Users** gain an admin flag (defaults to off).

**Generations**, one per user request:

* Owner.
* Our own unique reference, created before calling Pixelforge. It is sent as the idempotency key and inside the metadata.
* Pixelforge generation id (unique, empty until Pixelforge accepts the request).
* Prompt, size, quality, reference image URLs.
* Status: pending (not yet accepted by Pixelforge), queued, processing, succeeded, failed.
* Estimated typical and maximum customer price.
* Rates snapshot returned by Pixelforge.
* Token usage per type once succeeded.
* Actual provider cost and actual customer charge (both zero when failed).
* Image URL and its expiry time.
* Error code and user facing error message.
* Submission attempt count.
* Pixelforge's created, started and completed times.

Indexes on owner with creation time (history), status (polling), completion time (reports), and unique indexes on our reference and the Pixelforge id.

**Webhook events**, one per unique Pixelforge event id: event id (unique), type, related generation, raw payload, received time, processed time.

## Workflows

### Submitting

1. The form shows the price estimate as the user types. The server recalculates it on submit and never trusts the browser's figure.
2. Input is validated with the same limits Pixelforge applies: prompt up to 4,000 characters, the three sizes, the three qualities, up to 4 http(s) reference URLs.
3. The app saves a pending generation and hands the Pixelforge call to a background job. The user lands on their history straight away.
4. The job sends the request with our reference as the idempotency key, so a retry can never create or bill a second generation.

| Pixelforge answer | What the app does |
|---|---|
| 202 accepted | Store the Pixelforge id and rates, move status forward |
| 429 rate or concurrency limit, 503 overloaded | Retry after the Retry-After seconds, backing off further each time, up to 5 attempts |
| Timeout or network error | Retry the same way (safe because of the idempotency key) |
| 429 daily limit | Fail with "daily quota reached, try again tomorrow (UTC)" |
| 400 content policy | Fail with "prompt rejected by the content policy" |
| 422 validation | Fail and show Pixelforge's message for the field |
| 401 invalid key, 409 key reused | Fail with a generic message and log it for operators |
| Retries exhausted | Fail with "Pixelforge is busy, please try again" |

All of these failures cost the user nothing.

### Webhooks

1. Verify the signature against the raw body in constant time and reject timestamps older than 5 minutes. Invalid requests get a 400.
2. Record the event id. If it was already recorded, answer 2xx and do nothing else.
3. Answer 2xx quickly and process the event in a background job.
4. Find the generation by our reference in the metadata. This works even when the webhook arrives before the create request has returned. Fill in the Pixelforge id if it is still missing.

### Status and charging rules

These apply equally to webhooks, polling and create responses:

* Status only moves forward: pending, queued, processing, then succeeded or failed. Succeeded and failed never change again. A late processing event after success is ignored.
* Moving to succeeded calculates the provider cost from token usage and the generation's rates, applies the markup and stores both, in one database update that only succeeds if the generation is not already final. A second success event therefore finds nothing to update and cannot charge twice.
* Moving to failed sets the actual charge to zero.

### History

Newest first and paginated. Each item shows the prompt, size, quality, status badge, typical and maximum estimate, actual charge once known, created and completed times, the image, and the error message when failed. A detail view adds the token breakdown and cost per token type. Expired images show a placeholder instead of a broken image. Users only ever see their own generations.

### Admin dashboard

Admins only, everyone else gets a 403. Date range filter, defaulting to the last 30 days and capped at 92 days like Pixelforge's usage report.

* Totals: registered users, generations, succeeded, failed, provider cost, customer charges, profit.
* Daily table: generations, provider cost, customer charges, profit, and Pixelforge's reported cost for the same day, with any mismatch highlighted.
* Per user table: generations, provider cost, customer charges, profit.

## Build order

1. Settings and secrets: Pixelforge base URL, API key and webhook secret in the environment, listed in the example environment file.
2. Database changes for users, generations and webhook events.
3. Pricing: estimate and actual cost, nanodollar handling, markup.
4. Pixelforge client with error mapping, then the submit job.
5. Generate page with live estimate.
6. History page and detail view with live refresh.
7. Webhook endpoint and event processing.
8. Polling fallback.
9. Admin dashboard and reconciliation.

## Risks

* **Shared limits.** The 3 concurrent generations limit and 60 requests per minute apply across all keys on the account, so local testing can slow the deployed app.
* **Webhook before response.** Handled by matching on our own reference, not the Pixelforge id.
* **Database locking.** SQLite ignores row locks, so double charging is prevented by the conditional update, not by locks. That holds on MySQL and Postgres as well.
* **Queue worker.** Submissions and webhook processing need a running queue worker, both locally and when deployed.
* **Server clock.** Signature timestamps are checked against our clock, so a skewed clock rejects valid webhooks.
* **Retry pressure.** Retries count towards the rate limit, which is why attempts are capped.

## How we verify

Using Pixelforge's test keywords and dashboard:

| Scenario | Expected |
|---|---|
| Normal prompt | Succeeds, actual charge is provider cost × 1.25 rounded up, never above the maximum estimate |
| [fail] | Ends failed, charge zero |
| [blocked] | Fails at submission with the content policy message, nothing created at Pixelforge |
| [overloaded] | Retried, then fails with the busy message, charge zero |
| [duplicate] | Each event recorded once, charged once |
| [slow] | Stays processing for 60 to 90 seconds, then succeeds |
| 4 submissions at once | The fourth waits on the concurrency limit and then goes through |
| Resend an old event from the dashboard | Ignored, no state change |
| Webhook with a bad signature | Rejected with 400 |
| Webhook endpoint down, then restored | Polling brings the generation to its final state |
| Admin daily provider cost | Matches Pixelforge usage to the nanodollar |
| Non admin opens the admin page | 403 |
