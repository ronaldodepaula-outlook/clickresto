<?php
require_once __DIR__ . '/home_resto_dashboard_bootstrap.php';

if (!($homeRestoCanRender ?? false)) {
    return;
}

require __DIR__ . '/home_resto_dashboard_view.php';
