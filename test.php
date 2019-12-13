<?php
require 'vendor/autoload.php';

use ArunFung\PhpApollo\ApolloClient;

class TestApolloClient extends ApolloClient
{
    protected $env_example_path = __DIR__;

    protected $env_example = '.env.example';

    protected $env_path = __DIR__;

    protected $env = '.env';

}

$server = '';

$app_id = 0;

$namespaces = [
    "application",
    "datasource",
];

$testApolloClient = new TestApolloClient($server, $app_id, $namespaces);

//$testApolloClient->pullConfigs();

$testApolloClient->start();