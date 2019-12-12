
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

class TestApolloClient extends ApolloClient
{
    // 模板文件夹
    protected $env_example_path = __DIR__;
    // 模板文件名
    protected $env_example = '.env.example';
    // env 配置文件夹
    protected $env_path = __DIR__;
    // env 文件名
    protected $env = '.env';

}
// apollo 服务地址
$server = '';
// apollo 后台配置的 APP ID
$app_id = 0;
// apollo 后台配置的命名空间
$namespaces = [
    "application",
    "datasource",
];

$testApolloClient = new TestApolloClient($server,$app_id,$namespaces);

$testApolloClient->pullConfigs();
```
