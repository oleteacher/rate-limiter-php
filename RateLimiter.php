<?php
class RateLimiter {
    private int $limitForPeriod;
    private float $limitRefreshPeriod; // in seconds
    private float $timeoutDuration;   // in seconds
    private array $requests = [];     // Stores timestamps of requests

    public function __construct(int $limitForPeriod, float $limitRefreshPeriod, float $timeoutDuration) {
        $this->limitForPeriod = $limitForPeriod;
        $this->limitRefreshPeriod = $limitRefreshPeriod;
        $this->timeoutDuration = $timeoutDuration;
    }

    /**
     * Checks if a request can proceed within the rate limit.
     *
     * @return bool True if allowed, False otherwise.
     */
    public function allowRequest(): bool {
        $now = microtime(true);

        // Remove requests outside the current refresh window
        $this->requests = array_filter($this->requests, function ($timestamp) use ($now) {
            return ($now - $timestamp) <= $this->limitRefreshPeriod;
        });

        // Check if request can proceed
        if (count($this->requests) < $this->limitForPeriod) {
            $this->requests[] = $now; // Add the new request timestamp
            return true;
        }

        return false;
    }

    /**
     * Waits until a request is allowed or the timeout expires.
     *
     * @return bool True if allowed after waiting, False if timeout.
     */
    public function waitForRequest(): bool {
        $start = microtime(true);

        while ((microtime(true) - $start) <= $this->timeoutDuration) {
            if ($this->allowRequest()) {
                return true;
            }
            usleep(10000); // Sleep for 10ms to prevent busy-waiting
        }

        return false;
    }
}
?>
