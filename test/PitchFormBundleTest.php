<?php
namespace Pitch\Form;

use Closure;
use DateTime;
use Pitch\Annotation\PitchAnnotationBundle;
use Pitch\Form\Form;
use Pitch\Form\Fixtures\MyFormEntityFactory;
use stdClass;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelEvents;

class PitchFormBundleTest extends KernelTestCase
{
    protected EventDispatcher $dispatcher;

    protected static function getKernelClass()
    {
        return get_class(new class('test', true) extends Kernel
        {
            public function getProjectDir()
            {
                return $this->dir ??= sys_get_temp_dir() . '/PitchForm-' . uniqid() . '/';
            }

            public function registerBundles(): iterable
            {
                return [
                    new FrameworkBundle(),
                    new PitchAnnotationBundle(),
                    new PitchFormBundle(),
                ];
            }

            public function registerContainerConfiguration(LoaderInterface $loader)
            {
                $loader->load(function (ContainerBuilder $containerBuilder) {
                    $containerBuilder->setParameter('kernel.secret', 'secret');
                });

                $loader->load(function (ContainerBuilder $containerBuilder) {
                    $containerBuilder->setDefinition(
                        'fooFactory',
                        new Definition(MyFormEntityFactory::class),
                    );
                });
            }
        });
    }

    protected function setUp(): void
    {
        self::bootKernel();

        $this->dispatcher = self::$kernel->getContainer()->get('event_dispatcher');
    }

    public function testFormControllerGet()
    {
        $controller = new class {
            /**
             * @Form("Pitch\Form\Fixtures\MyFormType")
             */
            public function __invoke()
            {
            }
        };
        $request = new Request();

        $event = new ControllerEvent(self::$kernel, $controller, $request, null);
        $this->dispatcher->dispatch($event, KernelEvents::CONTROLLER);

        $this->assertInstanceOf(FormInterface::class, $request->attributes->get('form'));
        $this->assertSame(null, $request->attributes->get('data'));

        $this->assertInstanceOf(Closure::class, $event->getController());
        $controllerResult = $event->getController()();
        $this->assertSame($request->attributes->get('form'), $controllerResult);
        /** @var FormInterface $controllerResult */
        $this->assertFalse($controllerResult->isSubmitted());
    }

    public function testFormControllerPostInvalid()
    {
        $controller = new class
        {
            /**
             * @Form("Pitch\Form\Fixtures\MyFormType")
             */
            public function __invoke()
            {
            }
        };
        $request = new Request([], [
            'form' => [
                'someDate' => '2001-02-03',
            ],
        ], [], [], [], ['REQUEST_METHOD' => 'POST']);
        $this->assertEquals('POST', $request->getMethod());

        $event = new ControllerEvent(self::$kernel, $controller, $request, null);
        $this->dispatcher->dispatch($event, KernelEvents::CONTROLLER);

        $this->assertInstanceOf(FormInterface::class, $request->attributes->get('form'));
        $this->assertSame(null, $request->attributes->get('data'));

        $this->assertInstanceOf(Closure::class, $event->getController());

        $controllerResult = $event->getController()();
        $this->assertSame($request->attributes->get('form'), $controllerResult);
        /** @var FormInterface $controllerResult */

        $this->assertTrue($controllerResult->isSubmitted());
        $this->assertFalse($controllerResult->isValid());
    }

    public function testFormControllerPost()
    {
        $controller = new class
        {
            /**
             * @Form("Pitch\Form\Fixtures\MyFormType")
             */
            public function __invoke()
            {
            }
        };
        $request = new Request([], [
            'form' => [
                'someText' => 'foo',
                'someDate' => '2001-02-03',
            ],
        ], [], [], [], ['REQUEST_METHOD' => 'POST']);

        $event = new ControllerEvent(self::$kernel, $controller, $request, null);
        $this->dispatcher->dispatch($event, KernelEvents::CONTROLLER);

        $this->assertInstanceOf(FormInterface::class, $request->attributes->get('form'));
        $this->assertEquals([
            'someText' => 'foo',
            'someDate' => new DateTime('2001-02-03'),
        ], $request->attributes->get('data'));

        $this->assertNotInstanceOf(Closure::class, $event->getController());
    }

    public function testCustomFormEntityFactory()
    {
        $controller = new class
        {
            /**
             * @Form("Pitch\Form\Fixtures\MyFormType", entityFactory="fooFactory")
             */
            public function __invoke()
            {
            }
        };
        $request = new Request([], [
            'form' => [
                'someText' => 'foo',
                'someDate' => '2001-02-03',
            ],
        ], [], [], [], ['REQUEST_METHOD' => 'POST']);

        $event = new ControllerEvent(self::$kernel, $controller, $request, null);
        $this->dispatcher->dispatch($event, KernelEvents::CONTROLLER);

        $this->assertInstanceOf(FormInterface::class, $request->attributes->get('form'));
        $data = $request->attributes->get('data');
        $this->assertInstanceOf(stdClass::class, $data);
        $this->assertEquals([
            'someText' => 'foo',
            'someDate' => new DateTime('2001-02-03'),
        ], (array) $data);
    }

    public function testInvalidEntityFactory()
    {
        $controller = new class
        {
            /**
             * @Form("Pitch\Form\Fixtures\MyFormType", entityFactory="barFactory")
             */
            public function __invoke()
            {
            }
        };
        $request = new Request();

        $this->expectExceptionMessage('Invalid entityFactory');

        $event = new ControllerEvent(self::$kernel, $controller, $request, null);
        $this->dispatcher->dispatch($event, KernelEvents::CONTROLLER);
    }

    public function testEntityConstructor()
    {
        $controller = new class
        {
            /**
             * @Form("Pitch\Form\Fixtures\MyFormType", entity="Pitch\Form\Fixtures\MyEntityType")
             */
            public function __invoke()
            {
            }
        };
        $request = new Request([], [
            'form' => [
                'someText' => 'foo',
                'someDate' => '2001-02-03',
            ],
        ], [], [], [], ['REQUEST_METHOD' => 'POST']);

        $event = new ControllerEvent(self::$kernel, $controller, $request, null);
        $this->dispatcher->dispatch($event, KernelEvents::CONTROLLER);

        $this->assertInstanceOf(FormInterface::class, $request->attributes->get('form'));
        $data = $request->attributes->get('data');
        $this->assertInstanceOf('Pitch\Form\Fixtures\MyEntityType', $data);
        $this->assertEquals([
            'someText' => 'foo',
            'someDate' => new DateTime('2001-02-03'),
        ], (array) $data);
    }

    public function testDisableControllerReplacement()
    {
        $controller = new class
        {
            /**
             * @Form("Pitch\Form\Fixtures\MyFormType", returnForm=false)
             */
            public function __invoke()
            {
            }
        };
        $request = new Request();

        $event = new ControllerEvent(self::$kernel, $controller, $request, null);
        $this->dispatcher->dispatch($event, KernelEvents::CONTROLLER);

        $this->assertSame($controller, $event->getController());
    }
}
