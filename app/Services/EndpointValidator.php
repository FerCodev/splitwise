<?php

namespace App\Services;

class EndpointValidator
{
    private $dnsResolver;

    public function __construct(?callable $dnsResolver = null)
    {
        $this->dnsResolver = $dnsResolver ?? [$this, 'dnsResolve'];
    }

    public function dnsResolve(string $host): array
    {
        $ips = [];

        $aRecords = @dns_get_record($host, DNS_A);
        if (is_array($aRecords)) {
            foreach ($aRecords as $r) {
                if (!empty($r['ip'])) {
                    $ips[] = $r['ip'];
                }
            }
        }

        $aaaaRecords = @dns_get_record($host, DNS_AAAA);
        if (is_array($aaaaRecords)) {
            foreach ($aaaaRecords as $r) {
                if (!empty($r['ipv6'])) {
                    $ips[] = $r['ipv6'];
                }
            }
        }

        return $ips;
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
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return false;
        }

        $packed = @inet_pton($ip);
        if ($packed === false) {
            return false;
        }

        if (strlen($packed) === 16) {
            $first = ord($packed[0]);
            if (($first & 0xfe) === 0xfc) {
                return false;
            }
            if ($first === 0xfe && (ord($packed[1]) & 0xc0) === 0x80) {
                return false;
            }
        }

        return true;
    }

    private function resolvesToPublicIp(string $host): bool
    {
        try {
            $ips = ($this->dnsResolver)($host);
        } catch (\Throwable $e) {
            return false;
        }

        if (!is_array($ips) || empty($ips)) {
            return false;
        }

        $found = false;
        foreach ($ips as $ip) {
            $ip = trim((string) $ip);
            if ($ip === '') {
                return false;
            }

            if (!$this->isPublicIp($ip)) {
                return false;
            }
            $found = true;
        }

        return $found;
    }
}
