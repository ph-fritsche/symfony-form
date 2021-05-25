<?php
namespace Pitch\Form\Fixtures;

use Pitch\Form\FormEntityFactoryInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

class MyFormEntityFactory implements FormEntityFactoryInterface
{
    public function createEntity(
        ControllerEvent $event,
        ?string $entityType
    ): object {
        return (object) [
            'someText' => '',
            'someDate' => null,
        ];
    }
}
