<?php
/**
 * M'Boost — Admin Login Redirect
 * This file redirects to the unified login page with admin mode.
 */
require_once __DIR__ . '/../config/config.php';
header('Location: ' . APP_URL . '/auth/login.php?mode=admin');
exit;
