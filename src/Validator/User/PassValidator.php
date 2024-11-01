<?php

namespace App\Validator\User;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Validation;

/**
 * The validator to validate password of the user data
 */
class PassValidator
{
    /**
     * Validation executor
     *
     * @param mixed $pass
     * @return true
     *
     * @throws ConstraintDefinitionException
     */
    public function validate(mixed $pass): true
    {
        $idValidator = Validation::createValidator();
        $violations = $idValidator->validate($pass, [
            new Assert\NotNull(),
            new Assert\NotBlank(),
            new Assert\Type('string'),
            new Assert\Length(['min' => 4, 'max' => 8])
        ]);

        if (count($violations) > 0) {
            throw new ConstraintDefinitionException(message: 'The user password is invalid.');
        }

        return true;
    }
}