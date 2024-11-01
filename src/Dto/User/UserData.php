<?php

namespace App\Dto\User;

/**
 * The data transfer object for user data
 */
class UserData
{
    /**
     * @param int|null $id
     * @param string|null $login
     * @param string|null $phone
     * @param string|null $pass
     */
    public function __construct(
        public ?int $id = null,
        public ?string $login = null,
        public ?string $phone = null,
        public ?string $pass = null
    ) {
    }
}