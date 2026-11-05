<?php

/*
| REST API routes, served under the /api/v1 prefix (bootstrap/app.php).
|
| The API contracts are in docs/architecture/api/api-contracts.md, and the Phase 06 backlog
| builds them module by module (app/Modules/<Module>/routes.php, included below).
| Checkout and payment (M21) are OUT OF SCOPE and get no routes.
*/

$moduleRoutes = glob(app_path('Modules/*/routes.php')) ?: [];
sort($moduleRoutes);

foreach ($moduleRoutes as $file) {
    require $file;
}
