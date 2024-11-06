<?php

namespace App\State\UserDataProcessor;

use App\Entity\User;
use App\Validator\User\IdValidator;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Throwable;

/**
 * The user data processor to delete user by ID provided in the request
 */
class DeleteProcessor
{
    /**
     * @param IdValidator $userIdValidator
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        private readonly IdValidator $userIdValidator,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * The processor executor
     *
     * @param Request $request
     * @return object
     */
    public function process(Request $request): object
    {
        try {
            $userId = $request->query->get('id');
            $this->userIdValidator->validate($userId);
            $user = $this->entityManager->getRepository(User::class)->find($userId);

            if ($user === null) {
                throw HttpException::fromStatusCode(statusCode: Response::HTTP_NOT_FOUND, message: 'The user by provided ID does not exist.');
            }

            $this->entityManager->remove($user);
            $this->entityManager->flush();

            return new Response(content: sprintf('The user with ID:"%s" has been successfully removed.', $request->query->get('id')), status: Response::HTTP_OK);
        } catch (ConstraintDefinitionException) {
            throw HttpException::fromStatusCode(statusCode: Response::HTTP_BAD_REQUEST, message: 'Validation error. Please check the input data and try again.');
        } catch (DBALException\UniqueConstraintViolationException) {
            throw HttpException::fromStatusCode(statusCode: Response::HTTP_UNPROCESSABLE_ENTITY, message: 'The user with the same login already exists. Please fix login and try again.');
        } catch (DBALException) {
            throw HttpException::fromStatusCode(statusCode: Response::HTTP_UNPROCESSABLE_ENTITY, message: 'Something went wrong follow data saving. Please try again later.');
        } catch (Throwable) {
            throw HttpException::fromStatusCode(statusCode: Response::HTTP_INTERNAL_SERVER_ERROR, message: 'Something went wrong. Please contact the support service.');
        }
    }
}