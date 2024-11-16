<?php
require_once 'RateLimiter.php';

// Create a RateLimiter instance with custom parameters
$rateLimiter = new RateLimiter(
    limitForPeriod: 2,        		 // Max 2 requests
    limitRefreshPeriod: 1.0,   // Reset limit every 1 second
    timeoutDuration: 0.1       // Timeout after 500 milliseconds
);

return $rateLimiter;
?>
