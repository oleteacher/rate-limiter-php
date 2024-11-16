<?php
require_once 'rate_limiter_config.php';

/** @var RateLimiter $rateLimiter */
$rateLimiter = require 'rate_limiter_config.php';

// Simulate requests
for ($i = 0; $i < 10; $i++) {
    if ($rateLimiter->allowRequest()) {
        echo "Request $i: Allowed\n";
    } else {
        echo "Request $i: Rate limit exceeded\n";
        if ($rateLimiter->waitForRequest()) {
            echo "Request $i: Allowed after waiting\n";
        } else {
            echo "Request $i: Denied after timeout\n";
        }
    }

    usleep(200000); // Simulate delay between requests (200ms)
}
?>
