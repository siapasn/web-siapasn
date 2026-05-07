<?php

namespace App\Services;

use Predis\Client as PredisClient;

/**
 * RedisService
 *
 * Wrapper Predis yang membaca konfigurasi dari master_aplikasi.
 * - Production (CI_ENVIRONMENT=production): gunakan Unix socket
 * - Development/Staging: gunakan TCP host:port
 */
class RedisService
{
    protected PredisClient $client;

    public function __construct()
    {
        $db      = \Config\Database::connect();
        $rows    = $db->table('master_aplikasi')
            ->whereIn('config_key', ['redis_socket','redis_host','redis_port','redis_password','redis_db'])
            ->get()->getResultArray();

        $cfg = [];
        foreach ($rows as $row) {
            $cfg[$row['config_key']] = $row['config_value'];
        }

        $env      = env('CI_ENVIRONMENT', 'development');
        $socket   = $cfg['redis_socket'] ?? '';
        $host     = $cfg['redis_host']   ?? '127.0.0.1';
        $port     = (int) ($cfg['redis_port']   ?? 6379);
        $password = $cfg['redis_password'] ?? '';
        $db_index = (int) ($cfg['redis_db']     ?? 0);

        // Production + socket tersedia → pakai Unix socket
        if ($env === 'production' && $socket !== '') {
            $params = ['scheme' => 'unix', 'path' => $socket];
        } else {
            $params = ['scheme' => 'tcp', 'host' => $host, 'port' => $port];
        }

        $options = [];
        if ($password !== '') {
            $params['password'] = $password;
        }
        if ($db_index > 0) {
            $params['database'] = $db_index;
        }

        $this->client = new PredisClient($params, $options);
    }

    public function getClient(): PredisClient
    {
        return $this->client;
    }

    public function set(string $key, string $value, int $ttl = 0): void
    {
        if ($ttl > 0) {
            $this->client->setex($key, $ttl, $value);
        } else {
            $this->client->set($key, $value);
        }
    }

    public function get(string $key): ?string
    {
        $val = $this->client->get($key);
        return $val !== null ? (string) $val : null;
    }

    public function delete(string $key): void
    {
        $this->client->del([$key]);
    }

    public function exists(string $key): bool
    {
        return (bool) $this->client->exists($key);
    }
}
