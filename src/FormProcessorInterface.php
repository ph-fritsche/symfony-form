<?php
namespace Pitch\Form;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

interface FormProcessorInterface
{
    public function handleFormRequest(
        FormInterface $form,
        Request $request
    ): mixed;
}
