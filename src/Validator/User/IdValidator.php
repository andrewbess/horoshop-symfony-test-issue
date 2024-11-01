<?php

namespace App\Validator\User;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Validation;

/**
 * The validator to validate ID of the user data
 */
class IdValidator
{
    /**
     * Validation executor
     *
     * @param mixed $id
     * @return true
     *
     * @throws ConstraintDefinitionException
     */
    public function validate(mixed $id): true
    {
        $idValidator = Validation::createValidator();
        $violations = $idValidator->validate($id, [
            new Assert\NotNull(),
            new Assert\NotBlank(),
            new Assert\Type('numeric'),
            new Assert\GreaterThan(0)
        ]);

        if (count($violations) > 0) {
            throw new ConstraintDefinitionException(message: 'The user ID is invalid.');
        }

        return true;
    }
}