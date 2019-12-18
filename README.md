<p align="center">
<a href="https://travis-ci.com/arunfung/php-apollo"><img src="https://travis-ci.com/arunfung/php-apollo.svg?branch=master" alt="Build Status"></a>
<a href="https://packagist.org/packages/arunfung/php-apollo"><img src="https://poser.pugx.org/arunfung/php-apollo/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/arunfung/php-apollo"><img src="https://poser.pugx.org/arunfung/php-apollo/v/stable" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/arunfung/php-apollo"><img src="https://poser.pugx.org/arunfung/php-apollo/license" alt="License"></a>
</p>

# [携程 Apollo 配置中心](https://github.com/ctripcorp/apollo) PHP 客户端

### 介绍
Apollo 配置中心 PHP client

#### 环境依赖

- php version >= 7.1

### 安装

通过 composer 安装:

``` bash
composer require arunfung/php-apollo
```

### 使用

```php
require 'vendor/autoload.php';

use ArunFung\PhpApollo\ApolloClient;

// apollo 服务地址
$server = '';
// apollo 后台配置的 APP ID
$app_id = 0;
// apollo 后台配置的命名空间
$namespaces = [
    "application",
    "datasource",
];

// 实例化 Apollo Client
$testApolloClient = new ApolloClient($server,$app_id,$namespaces);
// 模板文件夹
$testApolloClient->setEnvExamplePath(__DIR__);
// 模板文件名
$testApolloClient->setEnvExample('.env.example');
// env 配置文件夹
$testApolloClient->setEnvPath(__DIR__);
// env 文件名
$testApolloClient->setEnv('.env');

// 拉取 Apollo 的配置写入本地配置文件（适合定时或者单次触发拉取配置）
$testApolloClient->pullConfigs();

// 开启应用感知配置更新并写入本地配置文件
$testApolloClient->start();
```

##### 本地缓存管理

```php
// 配置默认缓存目录 /var/www/php-apollo-cache
// 也可以自定义缓存目录
$cache_path = '/data/apollo';
$testApolloClient->setCachePath($cache_path);
```
