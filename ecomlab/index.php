<?php
/**
 * index.php — the application entry point.
 *
 * Requires the core (session + helpers) and loads the home view. Keeping the
 * entry point this thin is the MVC ideal: routing/config here, markup in views.
 */
require_once __DIR__ . '/core/core.php';
require_once __DIR__ . '/views/home.php';
