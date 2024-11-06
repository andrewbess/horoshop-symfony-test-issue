<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use ApiPlatform\Symfony\Security\Exception\AccessDeniedException;
use App\Dto\User\UserData;
use App\Repository\UserRepository;
use App\State\UserDataProcessor;
use App\State\UserDataProvider;
use ArrayObject;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQUE_USER_LOGIN', fields: ['login'])]
#[UniqueEntity(fields: ['login'], message: 'There is already an account with this login')]
#[ApiResource(
    uriTemplate: 'users',
    operations: [
        new Get(
            stateless: false,
            openapi: new Model\Operation(
                responses: [
                    Response::HTTP_OK => new Model\Response(
                        description: 'User data.',
                        content: new ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'login' => ['type' => 'string'],
                                        'phone' => ['type' => 'string'],
                                        'pass' => ['type' => 'string']
                                    ]
                                ],
                                'example' => [
                                    'login' => 'john_doe',
                                    'phone' => '1234567',
                                    'pass' => '!uhb567?'
                                ]
                            ]
                        ])
                    ),
                    Response::HTTP_BAD_REQUEST => new Model\Response(
                        description: 'The input is invalid.',
                    ),
                    Response::HTTP_FORBIDDEN => new Model\Response(
                        description: 'Access denied. You tried to change user data, but it is not from your user.'
                    ),
                    Response::HTTP_NOT_FOUND => new Model\Response(
                        description: 'The user with provided ID does not exist.'
                    ),
                    Response::HTTP_INTERNAL_SERVER_ERROR => new Model\Response(
                        description: 'The server error. Something went wrong during the request processing.'
                    )
                ],
                summary: 'Get user data',
                description: 'Retrieves the user data by provided ID'
            ),
            security: "is_granted('ROLE_ADMIN') or object.login == user.getLogin()",
            provider: UserDataProvider::class,
            parameters: ['id' => new QueryParameter(schema: ['type' => 'integer', 'minimum' => 1], required: true)]
        ),
        new Post(
            stateless: false,
            status: Response::HTTP_OK,
            openapi: new Model\Operation(
                responses: [
                    Response::HTTP_OK => new Model\Response(
                        description: 'User data',
                        content: new ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'login' => ['type' => 'string'],
                                        'phone' => ['type' => 'string'],
                                        'pass' => ['type' => 'string'],
                                        'id' => ['type' => 'integer']
                                    ]
                                ],
                                'example' => [
                                    'login' => 'john_doe',
                                    'phone' => '1234567',
                                    'pass' => '!uhb567?',
                                    'id' => 123
                                ]
                            ]
                        ])
                    ),
                    Response::HTTP_BAD_REQUEST => new Model\Response(
                        description: 'The input is invalid.',
                    ),
                    Response::HTTP_FORBIDDEN => new Model\Response(
                        description: 'Access denied. You tried to change user data, but it is not from your user.'
                    ),
                    Response::HTTP_NOT_FOUND => new Model\Response(
                        description: 'The user with provided ID does not exist.'
                    ),
                    Response::HTTP_INTERNAL_SERVER_ERROR => new Model\Response(
                        description: 'The server error. Something went wrong during the request processing.'
                    )
                ],
                summary: 'Update user data',
                description: 'Updates user data and retrieves data of them',
                requestBody: new Model\RequestBody(
                    content: new ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'login' => ['type' => 'string', 'minLength' => 3, 'maxLength' => 8],
                                    'phone' => ['type' => 'string', 'minLength' => 5, 'maxLength' => 8],
                                    'pass' => ['type' => 'string', 'minLength' => 4, 'maxLength' => 8],
                                    'id' => ['type' => 'integer', 'exclusiveMinimum' => 0]
                                ]
                            ],
                            'example' => [
                                'login' => 'john_doe',
                                'phone' => '1234567',
                                'pass' => '!uhb567?',
                                'id' => 123
                            ]
                        ]
                    ])
                )
            ),
            class: UserData::class,
            normalizationContext: ['api_allow_update' => true],
            denormalizationContext: ['api_allow_update' => true],
            security: "is_granted('ROLE_ADMIN') or (is_granted('IS_AUTHENTICATED_FULLY') and object != null and object.login == user.getLogin())",
            read: true,
            write: true,
            provider: UserDataProvider::class,
            processor: UserDataProcessor::class
        ),
        new Put(
            stateless: false,
            openapi: new Model\Operation(
                responses: [
                    Response::HTTP_OK => new Model\Response(
                        description: 'The ID of the created user',
                        content: new ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'id' => ['type' => 'integer']
                                    ]
                                ],
                                'example' => [
                                    'id' => 123
                                ]
                            ]
                        ])
                    ),
                    Response::HTTP_BAD_REQUEST => new Model\Response(
                        description: 'The input is invalid.',
                    ),
                    Response::HTTP_UNPROCESSABLE_ENTITY => new Model\Response(
                        description: 'The user saving error. Something went wrong during the create user processing.',
                    ),
                    Response::HTTP_INTERNAL_SERVER_ERROR => new Model\Response(
                        description: 'The server error. Something went wrong during the request processing.'
                    )
                ],
                summary: 'Create a new user',
                description: 'Creates a new user and retrieves ID of them',
                requestBody: new Model\RequestBody(
                    content: new ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'login' => ['type' => 'string', 'minLength' => 3, 'maxLength' => 8],
                                    'phone' => ['type' => 'string', 'minLength' => 5, 'maxLength' => 8],
                                    'pass' => ['type' => 'string', 'minLength' => 4, 'maxLength' => 8]
                                ]
                            ],
                            'example' => [
                                'login' => 'john_doe',
                                'phone' => '1234567',
                                'pass' => '!uhb567?'
                            ]
                        ]
                    ])
                )
            ),
            security: "is_granted('PUBLIC_ACCESS')",
            processor: UserDataProcessor::class,
            extraProperties: [ OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false ]
        ),
        new Delete(
            stateless: false,
            status: Response::HTTP_OK,
            openapi: new Model\Operation(
                responses: [
                    Response::HTTP_OK => new Model\Response(
                        description: 'The user has been removed successfully',
                    ),
                    Response::HTTP_BAD_REQUEST => new Model\Response(
                        description: 'The input is invalid.',
                    ),
                    Response::HTTP_FORBIDDEN => new Model\Response(
                        description: 'Access denied. Only admin users have permissions to delete other users.'
                    ),
                    Response::HTTP_NOT_FOUND => new Model\Response(
                        description: 'The user with provided ID does not exist.'
                    ),
                    Response::HTTP_INTERNAL_SERVER_ERROR => new Model\Response(
                        description: 'The server error. Something went wrong during the request processing.'
                    )
                ],
                summary: 'Delete user',
                description: 'Removes the user by provided ID',
            ),
            description: 'Delete User',
            security: "is_granted('ROLE_ADMIN')",
            processor: UserDataProcessor::class,
            parameters: ['id' => new QueryParameter(schema: ['type' => 'integer', 'minimum' => 1], required: true)]
        )
    ],
    exceptionToStatus: [
        AccessDeniedException::class => Response::HTTP_FORBIDDEN,
        AccessDeniedHttpException::class => Response::HTTP_FORBIDDEN
    ]
)]
final class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    #[ApiProperty(identifier: true)]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 8, unique: true)]
    private ?string $login = null;

    #[ORM\Column(type: 'string', length: 8)]
    private ?string $phone = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $pass = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): User
    {
        $this->id = $id;

        return $this;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): User
    {
        $this->login = $login;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): User
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPass(): ?string
    {
        return $this->pass;
    }

    public function setPass(string $pass): User
    {
        $this->pass = $pass;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->login;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): User
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->pass;
    }

    public function setPassword(string $password): User
    {
        $this->pass = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
