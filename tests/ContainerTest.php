<?php


namespace Studiow\Autowire\Test;

use PHPUnit\Framework\TestCase;
use League\Container\Container as LeagueContainer;
use Psr\Container\NotFoundExceptionInterface;
use Studiow\Autowire\Container;
use Studiow\Autowire\Test\Util\Configuration;
use Studiow\Autowire\Test\Util\ConfigurationAwareObject;
use Studiow\Autowire\Test\Util\HasConfiguration;

class ContainerTest extends TestCase
{


    public function testWrapContainer()
    {
        $container = new LeagueContainer();

        $wired = new Container($container);

        //make sure the proper container gets returned
        $this->assertSame($container, $wired->getContainer());

        //check the properties from psr/container
        $this->assertFalse($wired->has('nonExistingItem'));

        $container->share('testItem', []);
        $this->assertTrue($wired->has('testItem'));
        $this->assertSame($container->get('testItem'), $wired->get('testItem'));

        $this->expectException(NotFoundExceptionInterface::class);
        $wired->get('nonExistingItem');
    }


    public function testAutowiring()
    {
        $container = new LeagueContainer();


        $sharedConfig = new Configuration();
        $container->share(Configuration::class, $sharedConfig);
        $container->share(ConfigurationAwareObject::class);

        $wired = new Container($container);
        $wired->attach(HasConfiguration::class, function (HasConfiguration $entry, Container $container) {
            $entry->setConfiguration(
                $container->get(Configuration::class)
            );
        });
        $obj = $wired->get(ConfigurationAwareObject::class);
        $this->assertSame($sharedConfig, $obj->getConfiguration());

    }


}