<?php
namespace Pitch\Form\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Pitch\Form\DependencyInjection\PitchFormExtension;
use Pitch\Form\EventSubscriber\ControllerSubscriber;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class FormEntityFactoryPassTest extends TestCase
{
    protected ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();

        $this->container->setDefinition(
            ControllerSubscriber::class,
            new Definition(ControllerSubscriber::class),
        );
    }

    public function testDisableReturnForm()
    {
        $this->container->setParameter('pitch_form.returnForm', false);

        (new FormEntityFactoryPass())->process($this->container);

        $this->assertFalse($this->container->getDefinition(ControllerSubscriber::class)->getArgument('$returnForm'));
    }
}
