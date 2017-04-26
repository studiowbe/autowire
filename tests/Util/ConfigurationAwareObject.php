<?php


namespace Studiow\Autowire\Test\Util;


class ConfigurationAwareObject implements HasConfiguration
{

    private $configuration;

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function setConfiguration(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }
}