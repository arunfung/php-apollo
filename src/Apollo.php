<?php

namespace ArunFung\PhpApollo;

use ArunFung\PhpApollo\Exceptions\ApolloException;
use ArunFung\PhpApollo\Exceptions\BadBaseUrlException;

class Apollo
{
    /** @var string apollo server */
    protected $server = '';

    /** @var int project app id */
    protected $app_id = 0;

    /** @var array project namespaces */
    protected $namespaces = [];

    /** @var string apollo cluster name */
    protected $cluster = 'default';

    /** @var string client ip */
    protected $ip;

    protected $cache_path = '/var/www/php-apollo-cache';

    protected $env_example_path = '';

    protected $env_example = '/.env.example';

    protected $env_path = '';

    protected $env = '/.env';

    private $last_configs = [];

    /**
     * @param $namespace
     * @return string
     * @throws ApolloException
     */
    public function getHttpCacheConfigsPath(string $namespace)
    {
        $params = ['ip' => $this->getIp()];
        $path = sprintf('/configfiles/%d/%s/%s?', $this->getAppId(), $this->getCluster(), $namespace) . urlencode(http_build_query($params));

        return $path;
    }

    /**
     * @param string $namespace
     * @return string
     * @throws ApolloException
     */
    public function getNoHttpCacheConfigsPath(string $namespace)
    {
        $params['ip'] = $this->getIp();
        $releaseKey = $this->getReleaseKey($namespace);
        if ($releaseKey) {
            $params['releaseKey'] = $releaseKey;
        }
        $query = urlencode(http_build_query($params));
        $path = sprintf('/configs/%d/%s/%s?', $this->getAppId(), $this->getCluster(), $namespace) . $query;

        return $path;
    }

    private function getReleaseKey(string $namespace)
    {
        $last_config = $this->getLastConfig($namespace);
        $this->last_configs[$namespace] = $last_config;
        return $last_config['releaseKey'] ?? '';
    }

    public function getNotificationsPath(array $notifications)
    {
        $params = [
            'appId' => $this->getAppId(),
            'cluster' => $this->getCluster(),
            'notifications' => json_encode($notifications)
        ];
        $path = '/notifications/v2?' . urlencode(http_build_query($params));
        return $path;
    }

    public function getConfigs(string $namespace, string $result)
    {
        if (!empty($result)) {
            $this->last_configs[$namespace] = json_decode($result, true);
            $config_file = $this->getLastConfigFile($namespace);
            file_put_contents($config_file, $result);
        }
        $last_configs = $this->last_configs[$namespace] ?? [];
        $configs = $last_configs['configurations'] ?? [];
        return $configs;
    }

    private function getLastConfig(string $namespace)
    {
        $last_config = [];
        $config_file = $this->getLastConfigFile($namespace);
        if (file_exists($config_file)) {
            $last_config = json_decode(file_get_contents($config_file), true);
        }
        return $last_config;
    }

    private function getLastConfigFile($namespace)
    {
        $last_cache_path = sprintf('%s/%d', $this->cache_path, $this->getAppId());
        if (!file_exists($last_cache_path)) {
            mkdir($last_cache_path, 0766, true);
        }
        $last_config_file = sprintf('%s/config-%s.php', $last_cache_path, $namespace);
        return $last_config_file;
    }

    /**
     * @return string
     * @throws BadBaseUrlException
     */
    public function getServer(): string
    {
        if (empty($this->server)) {
            throw new BadBaseUrlException('server url is empty!');
        }
        return rtrim($this->server, '/');
    }

    /**
     * @param string $server
     */
    public function setServer(string $server)
    {
        $this->server = $server;
    }

    /**
     * @return int
     * @throws ApolloException
     */
    public function getAppId(): int
    {
        if (empty($this->app_id)) {
            throw new ApolloException('app id is empty!');
        }
        return $this->app_id;
    }

    /**
     * @param int $app_id
     */
    public function setAppId(int $app_id)
    {
        $this->app_id = $app_id;
    }

    /**
     * @return string
     */
    public function getCluster(): string
    {
        return $this->cluster;
    }

    /**
     * @param string $cluster
     * @return $this
     * @throws ApolloException
     */
    public function setCluster(string $cluster)
    {
        if (empty($cluster)) {
            throw new ApolloException('cluster name is empty!');
        }
        $this->cluster = $cluster;
        return $this;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        if (empty($this->ip)) {
            $this->ip = getHostByName(getHostName()) ?? '127.0.0.1';
        }
        return $this->ip;
    }
}