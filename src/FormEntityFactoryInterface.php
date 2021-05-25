<?php
namespace Pitch\Form;

use Symfony\Component\HttpKernel\Event\ControllerEvent;

interface FormEntityFactoryInterface
{
    public function createEntity(
        ControllerEvent $controllerEvent,
        ?string $entityClass
    ): object;
}
