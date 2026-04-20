---
title: Queueing update pushes
weight: 4
---

When a `MobilePass` is updated, the package notifies Apple (via APNs) or Google (via the Wallet REST API) so the user's device picks up the change. These HTTP calls can take a few hundred milliseconds each, and you probably don't want your request blocked waiting on them.

The package dispatches the notification through a `PushPassUpdateJob`. By default the job runs synchronously. Set a queue connection and it runs asynchronously instead.

```bash
MOBILE_PASS_QUEUE_CONNECTION=redis
MOBILE_PASS_QUEUE_NAME=wallet
```

With these set, every update push goes onto the `wallet` queue on the `redis` connection. Your web request returns immediately, and a queue worker handles the actual push a moment later.

## Default synchronous behaviour

If `MOBILE_PASS_QUEUE_CONNECTION` is unset (or explicitly `null`), the job runs synchronously in the same process. This is the right default for development and for low-traffic apps: no queue worker needed, and you see errors from Apple or Google immediately.

## When to turn it on

Turn queueing on when:

- You have enough traffic that a 500ms per-update delay hurts.
- You're updating multiple passes at once (a batch operation, a bulk notification).
- You already have a queue worker running for other jobs.

## Retries

Because the job is a standard Laravel `ShouldQueue` job, normal queue conventions apply. Tune retries, delays, and failure handling in your queue config or on the job class if you need to customise it.
