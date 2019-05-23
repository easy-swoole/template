<?php


namespace EasySwoole\Template;


interface RenderInterface
{
    public function render(string $template,array $data = [],array $options = []);
}