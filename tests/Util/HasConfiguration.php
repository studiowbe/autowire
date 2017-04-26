<?php


namespace Studiow\Autowire\Test\Util;


interface HasConfiguration
{
    public function getConfiguration(): Configuration;

    public function setConfiguration(Configuration $configuration);
}