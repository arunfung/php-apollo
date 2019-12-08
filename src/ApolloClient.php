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

    protected $namespaces = [
        "application",
        "datasource",
    ];

    public function __construct()
    {
        $this->client = new Client(['base_uri' => $this->getServer()]);
    }

    public function pullConfigs()
    {
        $this->getNoHttpCacheConfigs($this->generateEnv());
    }

    public function getNoHttpCacheConfigs(callable $callback)
    {
        $configs = $this->requestApolloServer(function ($namespace) {
            return $this->getNoHttpCacheConfigsPath($namespace);
        });

        $callback($configs);
    }

    private function requestApolloServer(callable $callback = null)
    {
        $configs = [];
        if (!is_array($this->namespaces) || empty($this->namespaces)) {
            throw new ApolloException('apollo namespaces is not array or empty');
        }

        foreach ($this->namespaces as $namespace) {
            $result = '';
            $uri = $callback($namespace);
            try {
                $response = $this->client->get($uri, ['timeout' => $this->timeout]);

                if ($response->getStatusCode() == 200) {
                    $result = $response->getBody()->getContents();
                }
            } catch (Exception $e) {
                echo sprintf('[%s] Pull the config failed！{%s}', date('Y-m-d H:i:s'), $e->getMessage()) . "\n";
            }

            $configs = array_merge($configs, $this->getConfigs($namespace, $result));
        }
        return $configs;
    }

    private function generateEnv()
    {
        return function ($configs) {
            if (empty($configs)) {
                echo sprintf('[%s] configs is empty!', date('Y-m-d H:i:s')) . "\n";
                return;
            }
            $env_example = $this->envToArray($this->env_example_path . $this->env_example);
            $env = [];
            foreach ($env_example as $key => $value) {
                if ($key) {
                    $env[$key] = $configs[$key] ?? '';
                } else {
                    $env[] = '';
                }
            }
            $this->arrayToEnv($env, $this->env_path . $this->env);
        };
    }

    public function envToArray($env_file_path)
    {
        if (!file_exists($env_file_path)) {
            throw new ApolloException('env file is not exists!');
        }
        $variables = [];
        $content = file_get_contents($env_file_path);
        $lines = explode("\n", $content);
        if ($lines) {
            foreach ($lines as $line) {
                if ($line !== "") {
                    $equalsLocation = strpos($line, '=');
                    $key = substr($line, 0, $equalsLocation);
                    $value = substr($line, ($equalsLocation + 1), strlen($line));
                    $variables[$key] = $value;
                } else {
                    $variables[] = "";
                }
            }
        }
        return $variables;
    }

    public static function arrayToEnv($array, $env_file_path)
    {
        $env = "";
        $position = 0;
        foreach ($array as $key => $value) {
            $position++;
            if ($value !== "" || !is_numeric($key)) {
                $env .= strtoupper($key) . "=" . $value;
                if ($position != count($array)) {
                    $env .= "\n";
                }
            } else {
                $env .= "\n";
            }
        }
        file_put_contents($env_file_path, $env);
    }
}