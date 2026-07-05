<?php

namespace App\Services;

class EndpointValidator
{
    private $dnsResolver;

    public function __construct(?callable $dnsResolver = null)
    {
        $this->dnsResolver = $dnsResolver ?? 'gethostbyname';
    }

    public function isValid(string $endpoint): bool
    {
        $url = filter_var($endpoint, FILTER_VALIDATE_URL);
        if (!$url) {
            return false;
        }

        $parsed = parse_url($url);
        $scheme = $parsed['scheme'] ?? '';

        if ($scheme !== 'https') {
            return false;
        }

        if (!empty($parsed['user']) || !empty($parsed['pass'])) {
            return false;
        }

        $host = $parsed['host'] ?? '';
        if ($host === '') {
            return false;
        }

        if (!$this->isHostAllowed($host)) {
            return false;
        }

        return true;
    }

    public function isHostAllowed(string $host): bool
    {
        $hostLower = strtolower($host);

        if (in_array($hostLower, ['localhost', '127.0.0.1', '::1', '0.0.0.0'], true)) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
            return $this->isPublicIp($host);
        }

        return $this->resolvesToPublicIp($host);
    }

    private function isPublicIp(string $ip): bool
    {
        return (bool) filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    private function resolvesToPublicIp(string $host): bool
    {
        $resolved = ($this->dnsResolver)($host);

        if ($resolved === false || $resolved === $host || $resolved === '') {
            return false;
        }

        $ips = is_array($resolved) ? $resolved : [$resolved];

        foreach ($ips as $ip) {
            $ip = trim((string) $ip);
            if ($ip === '') {
                continue;
            }

            if (!$this->isPublicIp($ip)) {
                return false;
            }
        }

        return true;
    }
}
