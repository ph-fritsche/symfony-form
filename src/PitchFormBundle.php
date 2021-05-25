<?php

namespace Pitch\Form;

use Pitch\Form\DependencyInjection\Compiler\FormEntityFactoryPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PitchFormBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new FormEntityFactoryPass());
    }
}
