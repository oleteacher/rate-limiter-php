<?php

class RateLimiter {
    private int $limitForPeriod;
    private float $limitRefreshPeriod; // in seconds
    private float $timeoutDuration;   // in seconds
    private array $requests = [];     // Stores timestamps of requests
    private $lock;                    // Lock for thread safety

    /**
     * Constructor for RateLimiter.
     * 
     * @param int $limitForPeriod Maximum number of requests allowed in the period.
     * @param float $limitRefreshPeriod Time period in seconds to refresh the limit.
     * @param float $timeoutDuration Maximum time in seconds to wait for a request to be allowed.
     */
    public function __construct(int $limitForPeriod, float $limitRefreshPeriod, float $timeoutDuration) {
        $this->limitForPeriod = $limitForPeriod;
        $this->limitRefreshPeriod = $limitRefreshPeriod;
        $this->timeoutDuration = $timeoutDuration;
        $this->lock = fopen(__DIR__ . '/rate_limiter.lock', 'c');

        if ($this->lock === false) {
            throw new RuntimeException('Unable to create lock file');
        }
    }

    public function __destruct() {
        if (is_resource($this->lock)) {
            fclose($this->lock);
        }
    }

    private function lock() {
        if (!flock($this->lock, LOCK_EX)) {
            throw new RuntimeException('Unable to acquire lock');
        }
    }

    private function unlock() {
        if (!flock($this->lock, LOCK_UN)) {
            throw new RuntimeException('Unable to release lock');
        }
    }

    /**
     * Checks if a request can proceed within the rate limit.
     *
     * @return bool True if allowed, False otherwise.
     */
    public function allowRequest(): bool {
        $this->lock();
        $now = microtime(true);

        // Remove requests outside the current refresh window
        $this->requests = array_filter($this->requests, function ($timestamp) use ($now) {
            return ($now - $timestamp) <= $this->limitRefreshPeriod;
        });

        // Check if request can proceed
        if (count($this->requests) < $this->limitForPeriod) {
            $this->requests[] = $now; // Add the new request timestamp
            $this->unlock();
            return true;
        }

        $this->unlock();
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
