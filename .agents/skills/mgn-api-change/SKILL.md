---
name: mgn-api-change
description: Add or change an MGN HTTP API endpoint, request contract, authentication boundary, or JSON response.
---

# MGN API change

Implement the requested API behavior while preserving neighboring contracts.

1. Read the matching route file, controller, request, repository/service, model, and closest Feature test. Read [references/api-map.md](references/api-map.md) when the route group, authentication boundary, or layer placement is unclear.
2. Keep the endpoint in the existing `/api/v1` route tree. Preserve route names and response envelopes unless the user requests a contract change.
3. Confirm whether the endpoint is public, user-authenticated, or admin-intended. `auth:sanctum` authenticates both tokenable models; add or retain the authorization check that proves the actor is allowed to perform the operation.
4. Validate input before side effects. Prefer a `FormRequest` for reusable or multi-field rules, and keep database-backed uniqueness/existence rules consistent with route-model keys.
5. Keep HTTP concerns in the controller and put non-trivial queries, writes, or integrations in the nearest repository/service. Follow the existing dependency-injection style.
6. Add or update a Pest Feature test covering the success response, persisted side effects, validation failure, authentication/authorization, and not-found behavior that the change affects. Use named routes rather than hard-coded URLs.
7. Run the focused Feature test and style-check the changed PHP files. Run PHPStan when signatures, model types, queries, or shared services changed.

If the endpoint touches chapters or chapter images, also use `$mgn-partitioned-content`. If it writes or removes files, also use `$mgn-media-storage`.

