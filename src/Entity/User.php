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
use App\Repository\UserRepository;
use App\State\UserDataProcessor;
use App\State\UserDataProvider;
use ArrayObject;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\Response;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource(
    uriTemplate: 'users',
    operations: [
        new Get(
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
            provider: UserDataProvider::class,
            parameters: ['id' => new QueryParameter(schema: ['type' => 'integer', 'minimum' => 1], required: true)]
        ),
        new Post(
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
            normalizationContext: ['api_allow_update' => true],
            denormalizationContext: ['api_allow_update' => true],
            processor: UserDataProcessor::class
        ),
        new Put(
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
            processor: UserDataProcessor::class,
            extraProperties: [ OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false ]
        ),
        new Delete(
            status: Response::HTTP_OK,
            openapi: new Model\Operation(
                responses: [
                    Response::HTTP_OK => new Model\Response(
                        description: 'The user has been removed successfully',
                    ),
                    Response::HTTP_BAD_REQUEST => new Model\Response(
                        description: 'The input is invalid.',
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
            processor: UserDataProcessor::class,
            parameters: ['id' => new QueryParameter(schema: ['type' => 'integer', 'minimum' => 1], required: true)]
        )
    ]
)]
final class User
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    #[ApiProperty(identifier: true)]
    private ?int $id = null;

    #[ORM\Column(length: 8)]
    private ?string $login = null;

    #[ORM\Column(length: 8, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 8)]
    private ?string $pass = null;

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
}
