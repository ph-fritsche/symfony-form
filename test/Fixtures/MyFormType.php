<?php
namespace Pitch\Form\Fixtures;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class MyFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('someText', TextType::class, [
            'required' => true,
        ]);

        $builder->add('someDate', DateType::class, [
            'widget' => 'single_text',
        ]);
    }
}
