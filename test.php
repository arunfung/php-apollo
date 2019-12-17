<?php
require 'vendor/autoload.php';

use ArunFung\PhpApollo\ApolloClient;

$server = '';

$app_id = 0;

$namespaces = [
    "application",
    "datasource",
];

$testApolloClient = new ApolloClient($server, $app_id, $namespaces);

$testApolloClient->setEnvExamplePath(__DIR__);
$testApolloClient->setEnvExample('.env.example');
$testApolloClient->setEnvPath(__DIR__);
$testApolloClient->setEnv('.env');
$testApolloClient->pullConfigs();

//$testApolloClient->start();