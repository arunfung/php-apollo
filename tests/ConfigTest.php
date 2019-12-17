<?php

use ArunFung\PhpApollo\Config;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testConfig()
    {
        $configs = [
            'APP_NAME' => 'Laravel',
            'APP_ENV' => 'dev',
            'APP_KEY' => 'jfksjfklsjlkfsjlksjksjdklsfdsfsdf',
            'APP_DEBUG' => 'true',
            'APP_URL' => 'http://localhost',
            'APP_TYPE' => 'type'
        ];

        $config = new Config();
        $config->setEnvExamplePath(dirname(__DIR__));
        $config->setEnvExample('/.env.example');
        $config->setEnvPath(dirname(__DIR__));
        $config->setEnv('/.env');
        $config->arrayToEnv($configs);

        $env = $config->envToArray(dirname(__DIR__) . '/.env');

        $this->assertEquals($configs, $env);
    }
}