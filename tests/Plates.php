<?php


namespace EasySwoole\Template\Test;


use EasySwoole\Template\RenderInterface;

class Plates implements RenderInterface
{

    public function render(string $template, array $data = [], array $options = []): ?string
    {
        // TODO: Implement render() method.
    }

    public function afterRender(?string $result, string $template, array $data = [], array $options = [])
    {
        // TODO: Implement afterRender() method.
    }

    public function onException(\Throwable $throwable): string
    {
        // TODO: Implement onException() method.
    }
}