<?php


namespace EasySwoole\Template;


interface RenderInterface
{
    public function render(string $template, array $data = [], array $options = []): ?string;

    public function onException(
        \Throwable $throwable,
        string $template,
        array $data = [],
        array $options = []
    ): void;
}
