<?php

class RateLimiter {
    private static $storageFile;
    private $requestsPerMinute;
    private $ipAddress;
    
    public function __construct($requestsPerMinute = 60) {
        $this->requestsPerMinute = $requestsPerMinute;
        $this->ipAddress = $_SERVER['REMOTE_ADDR'];
        self::$storageFile = __DIR__ . '/../storage/rate_limits.json';
    }
    
    public function check() {
        $limits = $this->getLimits();
        $now = time();
        $key = $this->ipAddress;
        foreach ($limits as $ip => $data) {
            if ($data['reset'] < $now) {
                unset($limits[$ip]);
            }
        }
        if (!isset($limits[$key]) || $limits[$key]['reset'] < $now) {
            $limits[$key] = [
                'count' => 1,
                'reset' => $now + 60 
            ];
            $this->saveLimits($limits);
            return true;
        }
        
        if ($limits[$key]['count'] >= $this->requestsPerMinute) {
            return false;
        }
        
        $limits[$key]['count']++;
        $this->saveLimits($limits);
        return true;
    }
    
    public function getRemainingRequests() {
        $limits = $this->getLimits();
        $key = $this->ipAddress;
        
        if (!isset($limits[$key])) {
            return $this->requestsPerMinute;
        }
        
        return max(0, $this->requestsPerMinute - $limits[$key]['count']);
    }
    
    public function getResetTime() {
        $limits = $this->getLimits();
        $key = $this->ipAddress;
        
        if (!isset($limits[$key])) {
            return time() + 60;
        }
        
        return $limits[$key]['reset'];
    }
    
    private function getLimits() {
        if (!file_exists(self::$storageFile)) {
            return [];
        }
        
        return json_decode(file_get_contents(self::$storageFile), true) ?? [];
    }
    
    private function saveLimits($limits) {
        file_put_contents(self::$storageFile, json_encode($limits));
    }
}