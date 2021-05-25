<?php
namespace Pitch\Form\Resources\config;

use Pitch\Form\EventSubscriber\ControllerSubscriber;
use Pitch\Form\FormProcessorInterface;
use Pitch\Form\Form\FormProcessor;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->defaults()
            ->autowire()
        ->set(ControllerSubscriber::class)
            ->tag('kernel.event_subscriber')
        ->set(FormProcessorInterface::class, FormProcessor::class)
    ;
};
