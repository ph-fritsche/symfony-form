<?php

namespace Pitch\Form\EventSubscriber;

use InvalidArgumentException;
use Pitch\Form\Form;
use Pitch\Form\FormEntityFactoryInterface;
use Pitch\Form\FormProcessorInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

class ControllerSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', -1024],
        ];
    }

    protected FormProcessorInterface $formProcessor;
    protected FormFactoryInterface $formFactory;

    /** @var FormEntityFactoryInterface[] */
    protected array $formEntityFactories;

    protected bool $returnForm;

    public function __construct(
        FormProcessorInterface $formProcessor,
        FormFactoryInterface $formFactory,
        array $formEntityFactories,
        bool $returnForm
    ) {
        $this->formProcessor = $formProcessor;
        $this->formFactory = $formFactory;
        $this->formEntityFactories = $formEntityFactories;
        $this->returnForm = $returnForm;
    }

    public function onKernelController(ControllerEvent $event)
    {
        $attributes = $event->getRequest()->attributes;
        /** @var Form|null */
        $annotation = $attributes->get('_' . Form::class)[0] ?? null;

        if ($annotation) {
            if ($annotation->entityFactory) {
                if (!isset($this->formEntityFactories[$annotation->entityFactory])) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'Invalid entityFactory "%s". The service is not available.',
                            $annotation->entityFactory,
                        ),
                    );
                }

                $entityFactory = $this->formEntityFactories[$annotation->entityFactory];
                $entity = $entityFactory->createEntity($event, $annotation->entity);
            } else {
                $entityClass = $annotation->entity;
                $entity = $entityClass
                    ? new $entityClass()
                    : null;
            }

            $form = $this->formFactory->createNamed(
                $annotation->name,
                $annotation->type,
                $entity,
                $annotation->options,
            );
            $attributes->set($annotation->formAttr, $form);

            $data = $this->formProcessor->handleFormRequest($form, $event->getRequest());
            $attributes->set($annotation->dataAttr, $data);

            if ($data === null && ($annotation->returnForm ?? $this->returnForm)) {
                $event->setController(fn() => $form);
            }
        }
    }
}
