# Template

## 实现原理
注册N个自定义进程做渲染进程，进程内关闭协程环境，并监听UNIXSOCK，客户端调用携程的client客户端发送数据给进程渲染，进程再返回结果给客户端，用来解决PHP模板引擎协程安全问题。可以实现渲染接口，根据自己的喜好引入smarty或者是blade或者是其他引擎.

## Demo
```php
use EasySwoole\Template\Config;
use EasySwoole\Template\Render;
use EasySwoole\Template\RenderInterface;

class R implements RenderInterface
{

    public function render(string $template, array $data = [], array $options = []):?string
    {
        return 'asas';
    }

    public function afterRender(?string $result, string $template, array $data = [], array $options = [])
    {
        // TODO: Implement afterRender() method.
    }

    public function onException(Throwable $throwable):string
    {
        return $throwable->getMessage();
    }

}

$config = new Config();
$config->setRender(new R());
$render = new Render($config);

$http = new swoole_http_server("0.0.0.0", 9501);
$http->on("request", function ($request, $response)use($render) {
    $response->end($render->render('a.html'));
});
$render->attachServer($http);

$http->start();
```