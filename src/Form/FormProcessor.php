<?php
namespace Pitch\Form\Form;

use Pitch\Form\FormProcessorInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class FormProcessor implements FormProcessorInterface
{
    public function handleFormRequest(
        FormInterface $form,
        Request $request
    ) {
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->validateRequiredFields($form);

            if ($form->isValid()) {
                return $form->getData();
            }
        }

        return null;
    }

    protected function validateRequiredFields(
        FormInterface $form
    ) {
        if ($form->isRequired() && $form->isEmpty()) {
            $form->addError(new FormError('required'));
        }
        foreach ($form as $child) {
            $this->validateRequiredFields($child);
        }
    }
}
