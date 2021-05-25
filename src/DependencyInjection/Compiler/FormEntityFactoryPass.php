<?php
namespace Pitch\Form\DependencyInjection\Compiler;

use Pitch\Form\DependencyInjection\PitchFormExtension;
use Pitch\Form\EventSubscriber\ControllerSubscriber;
use Pitch\Form\FormEntityFactoryInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Throwable;

class FormEntityFactoryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $formEntityFactories = [];
        foreach ($container->getDefinitions() as $id => $definition) {
            $class = $definition->getClass();
            try {
                if (!\class_exists($class)) {
                    continue;
                }
            } catch (Throwable $e) {
                continue;
            }

            if (\is_a($class, FormEntityFactoryInterface::class, true)) {
                $formEntityFactories[$id] = new Reference($id);
            }
        }

        $returnForm = $container->hasParameter(PitchFormExtension::ALIAS . '.returnForm')
            ? $container->getParameter(PitchFormExtension::ALIAS . '.returnForm')
            : true;

        $container->getDefinition(ControllerSubscriber::class)
            ->setArgument('$formEntityFactories', $formEntityFactories)
            ->setArgument('$returnForm', (bool) $returnForm);
    }
}
