<?php
namespace Pitch\Form;

use Attribute;
use Pitch\Annotation\AbstractAnnotation;
use Symfony\Component\Form\Extension\Core\Type\FormType;

/**
 * @Annotation
 */
#[Attribute]
class Form extends AbstractAnnotation
{
    public string $type = FormType::class;
    public string $name = 'form';
    public array $options = [];
    public ?string $entity = null;
    public ?string $entityFactory = null;
    public string $dataAttr = 'data';
    public string $formAttr = 'form';

    public ?bool $returnForm = null;

    public function setValue(string $type)
    {
        $this->type = $type;
    }
}
