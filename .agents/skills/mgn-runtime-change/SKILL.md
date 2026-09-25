---
name: mgn-runtime-change
description: Change the MGN Docker, Nginx, PHP-FPM, Horizon, scheduler, Redis, or deployment runtime.
---

# MGN runtime change

Keep the API and worker images compatible with the Compose topology and shared Laravel state.

1. Read [references/runtime-map.md](references/runtime-map.md) before changing a Dockerfile, Compose service, entrypoint, Nginx/FPM setting, supervisor program, scheduled command, queue, port, volume, or permission.
2. Trace the complete connection being changed: Compose service and network, container port, process listener, proxy/upstream, application config, and environment variable. Update every side of the contract together.
3. Preserve the separate `api` and `worker` image targets. The worker runs both Horizon and Laravel's scheduler under Supervisor; the API runs PHP-FPM behind Nginx.
4. Preserve non-root runtime operation and group-writable Laravel storage/cache behavior. Do not solve permission issues by making the whole repository world-writable.
5. Keep secrets in environment variables and `.env` mounts. Never bake `.env`, credentials, or production data into an image or committed config.
6. Do not start, stop, rebuild, publish, or deploy production services unless the user explicitly requests that external action. Configuration edits alone do not authorize a rollout.
7. Validate static configuration first. Use `docker compose config` when Docker is available; build only the affected target when a build is requested or needed to prove the change. Verify worker and API behavior separately for shared-image changes.

For application queue jobs or scheduled commands, also run their focused Pest tests and verify the queue name, retry behavior, and synchronous test behavior.

