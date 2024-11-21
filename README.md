# Rate Limiter in PHP
A pure PHP implementation of a rate limiter.

## Features
- Limit the number of requests within a specific time period.
- Timeout mechanism for requests exceeding the limit.
- Customizable parameters.

## Installation
Clone the repository:
```bash
git clone https://github.com/niteshapte/rate-limiter-php.git
```
## Usage
### Configuration
The rate limiter can be configured in ```rate_limiter_config.php```
```
$rateLimiter = new RateLimiter(
    limitForPeriod: 2,         // Max 2 requests
    limitRefreshPeriod: 1.0,   // Reset limit every 1 second
    timeoutDuration: 0.1       // Timeout after 500 milliseconds
);
```

## Example
Check the ```example.php``` file:
```
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
```
While I can't provide an exact number of users that can be active without causing server slow down, here are some factors to consider:

1. **Concurrency Handling:** The locking mechanism ensures thread safety, but excessive locking can lead to contention and slow down if too many requests are processed simultaneously.
2. **Request Processing:** The `allowRequest` method's performance depends on the number of stored timestamps and the efficiency of the `array_filter` function. With a high number of requests, this operation could become a bottleneck.
3. **Server Resources:** The actual number of users your server can handle depends on your server's CPU, memory, and I/O capabilities.

For a rough estimate:
- **150 Active Users:** Should be manageable with the current implementation, assuming moderate server resources.
- **500 Total Users:** If only a portion (e.g., 150) are active at any given time, the performance should remain acceptable.

### Recommendations:
- **Load Testing:** Perform load testing to determine the actual capacity and identify any bottlenecks.
- **Optimization:** Consider using a more scalable solution like Redis for storing timestamps if higher concurrency is expected.
- **Monitoring:** Continuously monitor server performance and adjust rate limiter parameters as needed.
