<?php

namespace App\State\UserDataProvider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\User\UserData;
use App\Entity\User as UserEntity;
use App\Repository\UserRepository;
use App\Validator\User\IdValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Throwable;

/**
 * The user data provider that provides user data by query params
 */
class GetDataByQueryProvider implements ProviderInterface
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
     * The provider executor
     *
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
            throw HttpException::fromStatusCode(statusCode: Response::HTTP_BAD_REQUEST, message: 'Bad request. Please check your request and try again.');
        } catch (ConstraintDefinitionException) {
            throw HttpException::fromStatusCode(statusCode: Response::HTTP_BAD_REQUEST, message: 'Validation error. Please check the input data and try again.');
        } catch (Throwable) {
            throw HttpException::fromStatusCode(statusCode: Response::HTTP_INTERNAL_SERVER_ERROR, message: 'Something went wrong. Please contact the support service.');
        }

        /** @var UserEntity $user */
        $user = $this->userRepository->find($userId);

        if ($user === null) {
            throw HttpException::fromStatusCode(statusCode: Response::HTTP_NOT_FOUND, message: 'The user by provided ID does not exist.');
        }

        return new UserData(
            login: $user->getLogin(),
            phone: $user->getPhone(),
            pass: 'The password is hashed and will not be able to show.'
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
