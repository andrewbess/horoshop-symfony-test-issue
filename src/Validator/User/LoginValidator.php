<?php

namespace App\Validator\User;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Validation;

/**
 * The validator to validate login of the user data
 */
class LoginValidator
{
    /**
     * Validation executor
     *
     * @param mixed $login
     * @return true
     *
     * @throws ConstraintDefinitionException
     */
    public function validate(mixed $login): true
    {
        $idValidator = Validation::createValidator();
        $violations = $idValidator->validate($login, [
            new Assert\NotNull(),
            new Assert\NotBlank(),
            new Assert\Type('string'),
            new Assert\Length(['min' => 3, 'max' => 8])
        ]);

        if (count($violations) > 0) {
            throw new ConstraintDefinitionException(message: 'The user login is invalid.');
        }

        return true;
    }
}