# EasySwoole Template

将非协程安全的 PHP 模板引擎放入独立渲染进程中执行，并通过 Unix Socket 与业务协程通信。

## 运行要求

- PHP 8.1 或更高版本
- Swoole 扩展
- EasySwoole Component 2.x

安装：

```bash
composer require easyswoole/template
```

## 工作原理

`Render` 会向 Swoole Server 注册指定数量的 `RenderWorker`。每个 worker 监听独立的 Unix Socket，并串行调用用户实现的 `RenderInterface`，避免同一个模板引擎实例被多个协程同时使用。业务协程通过客户端发送模板名称、数据和选项，worker 完成渲染后返回字符串或 `null`。

该方式适用于 Smarty、Blade、Plates、Think Template 等不适合直接在协程间共享实例的模板引擎。模板引擎依赖需要由项目自行安装。

## 实现渲染器

```php
<?php

use EasySwoole\Template\RenderInterface;

final class MyRenderer implements RenderInterface
{
    public function render(string $template, array $data = [], array $options = []): ?string
    {
        return sprintf(
            'template=%s, data=%s',
            $template,
            json_encode($data, JSON_UNESCAPED_UNICODE)
        );
    }

    public function onException(
        \Throwable $throwable,
        string $template,
        array $data = [],
        array $options = []
    ): void {
        error_log($throwable->getMessage());
    }
}
```

`onException()` 运行在渲染 worker 中，用于记录或上报渲染异常。异常信息不会作为响应返回给客户端；发生异常时 worker 会关闭本次连接。

## 配置并挂载服务

```php
<?php

use EasySwoole\Template\Exception\ClientException;
use EasySwoole\Template\Exception\ClientTimeoutException;
use EasySwoole\Template\Render;
use Swoole\Http\Server;

require 'vendor/autoload.php';

$render = new Render();
$config = $render->getConfig();
$config->setRender(new MyRenderer());
$config->setServerName('MyApplication');
$config->setWorkerNum(3);
$config->setTimeout(3);

$server = new Server('0.0.0.0', 9501);
$server->on('request', function ($request, $response) use ($render): void {
    try {
        $content = $render->render(
            'index.html',
            ['name' => 'EasySwoole'],
            ['cache' => true]
        );
        $response->end($content ?? '');
    } catch (ClientTimeoutException | ClientException $throwable) {
        error_log($throwable->getMessage());
        $response->status(503);
        $response->end('Template service unavailable');
    }
});

$render->attachServer($server);
$server->start();
```

`Render` 不实现单例。实例的创建和共享方式由调用方决定。

`attachServer()` 只能调用一次，并且必须事先配置 `RenderInterface`。重复挂载或未配置 renderer 时会抛出 `LogicException`。

## 配置项

| 配置 | 默认值 | 说明 |
| --- | --- | --- |
| `render` | `null` | `RenderInterface` 实现，挂载服务前必须配置 |
| `serverName` | `EasySwoole` | 进程名和 Unix Socket 文件名前缀；多实例应使用不同名称 |
| `tempDir` | `sys_get_temp_dir()` | Unix Socket 文件目录 |
| `workerNum` | `3` | 渲染进程数量，必须大于 0 |
| `timeout` | `3` | 单次客户端请求的总超时秒数，必须大于 0 |
| `onException` | `null` | 渲染进程自身发生运行时异常时的回调 |

进程级异常回调会收到原始异常和 EasySwoole Process 提供的全部上下文参数：

```php
$config->setOnException(
    static function (\Throwable $throwable, ...$context): void {
        error_log($throwable->getMessage());
    }
);
```

## Deadline 与客户端异常

每次 `render()` 会创建一个共享 deadline，连接、发送和接收共同消耗 `timeout` 配置的时间预算。

- deadline 耗尽时抛出 `ClientTimeoutException`。
- 非超时的连接、发送或接收错误抛出 `ClientException`。
- 两种异常都包含失败阶段和 Unix Socket 路径。
- `ClientException` 还包含 Swoole 的 `errCode` 和 `errMsg`。

失败阶段可能是 `connect`、`send` 或 `receive`。

## 进程管理

worker 启动后会以 PID 为 key 写入共享 `Swoole\Table`，退出时删除对应记录：

```php
$processTable = $render->getProcessTable();

foreach ($processTable as $pid => $row) {
    echo $row['pid'], PHP_EOL;
}
```

组件不提供 worker 重启指令。进程停止、拉起和异常恢复应由 Swoole Server 或调用方的外部进程管理机制负责。

## 测试

```bash
composer install
composer test
```
