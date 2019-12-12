<?php

namespace ArunFung\PhpApollo;

use ArunFung\PhpApollo\Exceptions\ApolloException;

class Apollo extends Config
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

    /** @var string cache path */
    protected $cache_path = '/var/www/php-apollo-cache';

    /** @var string last configs array */
    private $last_configs = [];

    public function getNotificationsPath(array $notifications)
    {
        $params = [
            'appId' => $this->getAppId(),
            'cluster' => $this->getCluster(),
            'notifications' => json_encode($notifications)
        ];
        return sprintf('/notifications/v2?%s', urlencode(http_build_query($params)));
    }

    /**
     * get no http cache configs path
     *
     * @param string $namespace
     * @return string
     */
    public function getNoHttpCacheConfigsPath(string $namespace)
    {
        $params['ip'] = $this->getIp();
        $releaseKey = $this->getReleaseKey($namespace);
        if ($releaseKey) {
            $params['releaseKey'] = $releaseKey;
        }
        $query = urlencode(http_build_query($params));
        $path = sprintf(
            '/configs/%d/%s/%s?',
            $this->getAppId(),
            $this->getCluster(),
            $namespace
            );
        $path .= $query;
        return $path;
    }

    /**
     * @param string $namespace
     * @return string
     */
    private function getReleaseKey(string $namespace): string
    {
        $last_config = $this->getLastConfig($namespace);
        return $last_config['releaseKey'] ?? '';
    }

    public function getConfigs(string $namespace, string $result)
    {
        if (!empty($result)) {
            $this->last_configs = array_merge((array) $this->last_configs, [$namespace => json_decode($result, true)]);
            $config_file = $this->getLastConfigFilePath($namespace);
            file_put_contents($config_file, $result);
        }

        $last_configs = $this->last_configs[$namespace] ?? [];
        return $last_configs['configurations'] ?? [];
    }

    /**
     * @param string $namespace
     * @return array
     */
    private function getLastConfig(string $namespace): array
    {
        $last_config = [];
        $config_file = $this->getLastConfigFilePath($namespace);
        if (file_exists($config_file)) {
            $last_config = json_decode(file_get_contents($config_file), true);
        }
        if (!empty($last_config)) {
            $this->last_configs = array_merge((array) $this->last_configs, [$namespace => $last_config]);
        }
        return $last_config;
    }

    /**
     * @param string $namespace
     * @return string
     */
    private function getLastConfigFilePath(string $namespace)
    {
        $cache_path = sprintf('%s/%d', rtrim($this->cache_path, '/'), $this->getAppId());
        if (!file_exists($cache_path)) {
            mkdir($cache_path, 0766, true);
        }
        return sprintf('%s/config-%s.php', $cache_path, $namespace);
    }

    /**
     * @return string
     */
    public function getServer(): string
    {
        return rtrim($this->server, '/');
    }

    /**
     * @param string $server
     * @throws ApolloException
     */
    public function setServer(string $server)
    {
        if (empty($server)) {
            throw new ApolloException('server url is empty!');
        }
        $this->server = $server;
    }

    /**
     * @return int
     */
    public function getAppId(): int
    {
        return $this->app_id;
    }

    /**
     * @param int $app_id
     * @throws ApolloException
     */
    public function setAppId(int $app_id)
    {
        if (empty($app_id)) {
            throw new ApolloException('app id is empty!');
        }
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
     * @throws ApolloException
     */
    public function setCluster(string $cluster)
    {
        if (empty($cluster)) {
            throw new ApolloException('cluster name is empty!');
        }
        $this->cluster = $cluster;
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

    /**
     * @return array
     */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    /**
     * @param array $namespaces
     * @throws ApolloException
     */
    public function setNamespaces(array $namespaces): void
    {
        if (empty($namespaces)) {
            throw new ApolloException('namespaces is empty!');
        }
        $this->namespaces = $namespaces;
    }
}