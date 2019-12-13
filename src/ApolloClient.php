<?php

namespace ArunFung\PhpApollo;

use ArunFung\PhpApollo\Exceptions\ApolloException;
use GuzzleHttp\Client;
use Exception;

/**
 * Class ApolloClient
 * @package ArunFung\PhpApollo
 */
class ApolloClient extends Apollo
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var int
     */
    protected $timeout = 3;

    /**
     * @var int
     */
    protected $interval_timeout = 70;

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
     * init apollo client
     *
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
        $this->setNotifications();
    }

    /**
     *
     */
    public function pullConfigs()
    {
        $this->getNoHttpCacheConfigs();
    }

    /**
     *
     */
    private function getNoHttpCacheConfigs()
    {
        $configs = $this->requestApolloServer(function ($namespace) {
            return $this->getNoHttpCacheConfigsPath($namespace);
        });

        $this->generateEnv($configs);
    }

    /**
     * request apollo server
     *
     * @param callable $callback
     * @return array
     */
    private function requestApolloServer(callable $callback)
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

    /**
     * listen change
     */
    private function listenChange()
    {
        do {
            $uri = $this->getNotificationsPath();
            try {
                $response = $this->client->get($uri, ['timeout' => $this->interval_timeout]);
                if ($response->getStatusCode() == 200) {

                    $result = $response->getBody()->getContents();
                    $result = json_decode($result, true);

                    foreach ($result as $value) {
                        $lastNotificationId = $this->notifications[$value['namespaceName']]['notificationId'];

                        if ($value['notificationId'] != $lastNotificationId) {
                            $this->notifications[$value['namespaceName']]['notificationId'] = $value['notificationId'];
                        }
                    };
                    $this->pullConfigs();
                    echo round(memory_get_usage() / 1024 / 1024, 2) . " MB\n";
                }
            } catch (Exception $e) {
                echo sprintf('[%s] listen change failed！{%s}', date('Y-m-d H:i:s'), $e->getMessage()) . "\n";
            }
        } while (true);
    }

    /**
     *  start listen
     */
    public function start()
    {
        $this->listenChange();
    }
}