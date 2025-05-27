<?php
require_once __DIR__ . '/../utils/RateLimiter.php';

class RateLimiterMiddleware {
    public static function check($requestsPerMinute = 60) {
        $rateLimiter = new RateLimiter($requestsPerMinute);
        if (!$rateLimiter->check()) {
            http_response_code(429); 
            header('Retry-After:' . ($rateLimiter->getResetTime() - time()).' seconds');
            header('X-RateLimit-Limit: ' . $requestsPerMinute);
            header('X-RateLimit-Remaining: 0');
            header('X-RateLimit-Reset: ' . $rateLimiter->getResetTime());
            
            echo json_encode([
                'error' => 'Rate limit exceeded. Please try again later.'
            ]);
            exit;
        }
        
        header('X-RateLimit-Limit: ' . $requestsPerMinute);
        header('X-RateLimit-Remaining: ' . $rateLimiter->getRemainingRequests());
        header('X-RateLimit-Reset: ' . $rateLimiter->getResetTime());
        
        return true;
    }
}