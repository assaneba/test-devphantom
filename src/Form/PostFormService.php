<?php

namespace App\Form;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormFactoryInterface;

class PostFormService
{
    private $formFactory;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function createPostForm($data = null, $options = []): FormInterface
    {
        return $this->formFactory->create(PostType::class, $data, $options);
    }

    public function handleFormSubmission(FormInterface $form, Request $request)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $form->getData(); 
        }

        $errors = $this->getErrorsFromForm($form);

        return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);

        return null;
    }

    private function getErrorsFromForm($form): array
    {
        $errors = [];
        foreach ($form->getErrors(true, true) as $error) {
            $field = $error->getOrigin();
            $fieldName = $field->getName();
            $errors[$fieldName] = $error->getMessage();
        }

        return $errors;
    }
}
