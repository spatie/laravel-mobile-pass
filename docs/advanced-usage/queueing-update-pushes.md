---
title: Queueing update pushes
weight: 4
---

When a `MobilePass` gets updated, the package notifies Apple (via APNs) or Google (via the Wallet REST API) so the user's device picks up the change. Those HTTP calls can take a few hundred milliseconds each, and you probably don't want your request sitting around waiting on them.

The notification goes out through a `PushPassUpdateJob`. By default the job runs synchronously. Set a queue connection and it runs asynchronously instead.

```bash
MOBILE_PASS_QUEUE_CONNECTION=redis
MOBILE_PASS_QUEUE_NAME=wallet
```

With those set, every update push goes onto the `wallet` queue on the `redis` connection. Your web request returns right away, and a queue worker handles the actual push a moment later.

## Default synchronous behaviour

If `MOBILE_PASS_QUEUE_CONNECTION` is unset (or explicitly `null`), the job runs synchronously in the same process. That's the right default for development and for low-traffic apps: no queue worker needed, and errors from Apple or Google surface immediately.

## When to turn it on

Turn queueing on when:

- You have enough traffic that a 500ms per-update delay starts to hurt.
- You're updating multiple passes at once (batch operations, bulk notifications).
- You already have a queue worker running for other jobs.

## Retries

The job is a standard Laravel `ShouldQueue` job, so normal queue conventions apply. Tune retries, delays, and failure handling in your queue config or on the job class if you need to customise it.
