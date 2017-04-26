# Container Autowire

This package provides a sort of autowiring wrapper for any psr/container-compatible container. 
 

## Installation
The (highly) recommended way to install studiow/autowire is by using [Composer](https://getcomposer.org/)

```bash
composer require studiow/autowire
```

## How to use
In my application, I have a Configuration object. There is also an interface for objects that can use this Configuration.
```php
class Configuration
{
    //...
}

interface HasConfiguration
{
    public function setConfiguration(Configuration $configuration);
    public function getConfiguration():Configuration
}
```

I like to use league/container as a DI container, so let's do just that:
```php
$container = new League\Container\Container();
$container->share(Configuration::class);
```

Now I would like to make sure that our Configuration object gets passed around to any object that needs it. One way to do this is by defining all classes in the container:
```php
class AClassWithConfiguration implements HasConfiguration{
    //...
}

$container->share(AClassWithConfiguration::class, function() use($container){
    $obj = new AClassWithConfiguration();
    $obj->setConfiguration($container->get(Configuration::class));
    return $obj;
});
```
This works great! Unfortunately, we'll need to this for any class that needs the Configuration object. Boring!

Let's see what we can do if we wrap our Container in an autowire container:

```php
$container = new League\Container\Container();
$container->share(Configuration::class);

$awContainer = Studiow\Autowire\Container($container); 
```
This looks familiar: it's the same DI container as before, but it now gets wrapped into an autowire container. 

Here's what we want the autowire container to do: if an object implements the HasConfiguration interface, use the setConfiguration method to pass on the Configuration object

```php
$awContainer->attach(HasConfiguration::class, function($item, $awContainer){
   $item->setConfiguration($awContainer->get(Configuration::class)); 
});
```

The attach method takes 2 arguments: the name of the interface, and a callback. The callback also has 2 arguments: the object we're dealing with, and the autowire container.
 
The callback will now automatically be executed when resolving objects from the container
 
```php
class AClassWithConfiguration implements HasConfiguration{
    //...
}

$container->share(AClassWithConfiguration::class});

$awContainer = Studiow\Autowire\Container($container); 
$obj = $awContainer->get(AClassWithConfiguration::class);

$obj->getConfiguration();
//Configuration object was automatically injected
```

If you want, you can bypass "add classes to container" too:

```php
$obj = new AClassWithConfiguration();
$obj = $awContainer->applyCallbacks($obj);
$obj->getConfiguration();
//Configuration object was injected
```


 