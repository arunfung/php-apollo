<?php

namespace ArunFung\PhpApollo;

/**
 * Class Config
 * @package ArunFung\PhpApollo
 */
abstract class Config
{
    /** @var string env example path */
    protected $env_example_path = '';

    /** @var string env example name */
    protected $env_example = '.env.example';

    /** @var string env path */
    protected $env_path = '';

    /** @var string env name */
    protected $env = '.env';

    protected function generateEnv($configs)
    {
        if (empty($configs)) {
            echo sprintf('[%s] configs is empty!', date('Y-m-d H:i:s')) . "\n";
            return;
        }
        $env_example = $this->getEnvExample();
        $env = [];
        foreach ($env_example as $key => $value) {
            if ($key) {
                $env[$key] = $configs[$key] ?? '';
            } else {
                $env[] = '';
            }
        }
        $this->arrayToEnv($env);
    }

    /**
     * @return array
     */
    protected function getEnvExample(): array
    {
        return $this->envToArray($this->getEnvExampleFilePath());
    }

    /**
     * get env file path
     *
     * @return string
     */
    private function getEnvFilePath()
    {
        if (!file_exists($this->env_path)) {
            mkdir($this->env_path, 0766, true);
        }
        return sprintf(
            '%s/%s',
            rtrim($this->env_path, '/'),
            ltrim($this->env, '/')
        );
    }

    private function getEnvExampleFilePath()
    {
        return sprintf(
            '%s/%s',
            rtrim($this->env_example_path, '/'),
            ltrim($this->env_example, '/')
        );
    }

    /**
     * Turn the env file into an array
     *
     * @param string $file_path
     * @return array
     */
    protected function envToArray(string $file_path): array
    {
        if (!file_exists($file_path)) {
            echo sprintf('[%s] %s is not exists!', date('Y-m-d H:i:s'), $file_path) . "\n";
            return [];
        }
        $variables = [];
        $content = file_get_contents($file_path);
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

    /**
     * Write the array to the env file
     *
     * @param array $array
     */
    protected function arrayToEnv(array $array)
    {
        $env_file_path = $this->getEnvFilePath();
        $env = '';
        $position = 0;
        $count = count($array);
        foreach ($array as $key => $value) {
            $position++;
            if ($value !== "" || !is_numeric($key)) {
                $env .= $key . "=" . $value;
                if ($position != $count) {
                    $env .= "\n";
                }
            } else {
                $env .= "\n";
            }
        }
        file_put_contents($env_file_path, $env);
    }
}