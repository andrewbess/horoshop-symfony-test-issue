<?php

namespace App\State\UserDataProvider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\User\UserData;
use App\Entity\User as UserEntity;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Throwable;

/**
 * The user data provider that provides data by request body
 */
class GetDataByContentBodyProvider implements ProviderInterface
{
    /**
     * @param UserRepository $userRepository
     */
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
    }

    /**
     * The provider executor
     *
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return object|null
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        try {
            $encoders = [new JsonEncoder()];
            $normalizers = [new ObjectNormalizer()];
            $serializer = new Serializer($normalizers, $encoders);

            $request = $this->getRequest($context);

            $user = $serializer->deserialize($request->getContent(), UserEntity::class, 'json');
        } catch (Throwable) {
            return null;
        }
        $userId = $user->getId();

        /** @var UserEntity $user */
        $user = $this->userRepository->find($userId);

        if ($user === null) {
            return null;
        }

        return new UserData(
            id: $user->getId(),
            login: $user->getLogin(),
            phone: $user->getPhone()
        );
    }

    /**
     * Get request from context
     *
     * @param array $context
     * @return Request
     * @throws BadRequestHttpException
     */
    private function getRequest(array $context): Request
    {
        /** @var Request|null $request */
        $request = $context['request'] ?? null;

        if ($request === null || $request->getMethod() !== 'POST') {
            throw new BadRequestHttpException(message: 'Bad request');
        }

        return $request;
    }
}
