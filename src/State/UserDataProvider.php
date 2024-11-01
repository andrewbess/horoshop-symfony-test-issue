<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\User\UserData;
use App\Entity\User as UserEntity;
use App\Repository\UserRepository;
use App\Validator\User\IdValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Throwable;

/**
 * The user data provider
 */
class UserDataProvider implements ProviderInterface
{
    /**
     * @param IdValidator $userIdValidator
     * @param UserRepository $userRepository
     */
    public function __construct(
        private readonly IdValidator $userIdValidator,
        private readonly UserRepository $userRepository
    ) {
    }

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return object
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object
    {
        try {
            $request = $this->getRequest($context);
            $userId = $request->query->get('id');
            $this->userIdValidator->validate($userId);
        } catch (BadRequestHttpException) {
            return new Response(content: 'Bad request. Please check your request and try again.', status: Response::HTTP_BAD_REQUEST);
        } catch (ConstraintDefinitionException) {
            return new Response(content: 'Validation error. Please check the input data and try again.', status: Response::HTTP_BAD_REQUEST);
        } catch (Throwable $exception) {
            return new Response(content: 'Something went wrong. Please contact the support service.', status: Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        /** @var UserEntity $user */
        $user = $this->userRepository->find($userId);

        if ($user === null) {
            return new Response(content: 'The user by provided ID does not exist.', status: Response::HTTP_NOT_FOUND);
        }

        return new UserData(
            login: $user->getLogin(),
            phone: $user->getPhone(),
            pass: $user->getPass()
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

        if ($request === null || $request->getMethod() !== 'GET') {
            throw new BadRequestHttpException(message: 'Bad request');
        }

        return $request;
    }
}
