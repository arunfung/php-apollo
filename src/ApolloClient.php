<?php

namespace ArunFung\PhpApollo;

use ArunFung\PhpApollo\Exceptions\ApolloException;
use GuzzleHttp\Client;
use Exception;

class ApolloClient extends Apollo
{
    protected $client;

    protected $notifications = [];

    protected $timeout = 3;

    /**
     * ApolloClient constructor.
     *
     * @param string $server
     * @param int $app_id
     * @param array $namespaces
     * @throws ApolloException
     */
    public function __construct(string $server, int $app_id, array $namespaces)
    {
        $this->init($server, $app_id, $namespaces);
        $this->client = new Client(['base_uri' => $this->getServer()]);
    }

    /**
     * @param string $server
     * @param int $app_id
     * @param array $namespaces
     * @throws ApolloException
     */
    private function init(string $server, int $app_id, array $namespaces)
    {
        $this->setServer($server);
        $this->setAppId($app_id);
        $this->setNamespaces($namespaces);
    }

    public function pullConfigs()
    {
        $this->getNoHttpCacheConfigs();
    }

    public function getNoHttpCacheConfigs()
    {
        $configs = $this->requestApolloServer(function ($namespace) {
            return $this->getNoHttpCacheConfigsPath($namespace);
        });

        $this->generateEnv($configs);
    }

    private function requestApolloServer(callable $callback = null)
    {
        $configs = [];

        foreach ($this->getNamespaces() as $namespace) {
            $result = '';
            $uri = $callback($namespace);
            try {
                $response = $this->client->get($uri, ['timeout' => $this->timeout]);

                if ($response->getStatusCode() == 200) {
                    $result = $response->getBody()->getContents();
                }
            } catch (Exception $e) {
                echo sprintf('[%s] Pull the %s config failed！{%s}',
                        date('Y-m-d H:i:s'), $namespace, $e->getMessage()) . "\n";
            }

            $configs = array_merge($configs, $this->getConfigs($namespace, $result));
        }
        return $configs;
    }
}