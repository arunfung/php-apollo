<?php
require 'vendor/autoload.php';

use ArunFung\PhpApollo\ApolloClient;

class TestApolloClient extends ApolloClient
{
    protected $server = '';
    protected $app_id = 0;

    protected $env_example_path = __DIR__;

    protected $env_example = '/.env.example';

    protected $env_path = __DIR__;

    protected $env = '/.env';

}

$testApolloClient = new TestApolloClient();

$testApolloClient->pullConfigs();