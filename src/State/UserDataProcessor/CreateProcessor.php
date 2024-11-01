<?php

namespace App\State\UserDataProcessor;

use App\Dto\User\UserData;
use App\Entity\User;
use App\Validator\User\LoginValidator;
use App\Validator\User\PassValidator;
use App\Validator\User\PhoneValidator;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Throwable;

/**
 * The user data processor to create new user by data provided in the request
 */
class CreateProcessor
{
    /**
     * @param LoginValidator $userLoginValidator
     * @param PhoneValidator $userPhoneValidator
     * @param PassValidator $userPassValidator
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        private readonly LoginValidator $userLoginValidator,
        private readonly PhoneValidator $userPhoneValidator,
        private readonly PassValidator $userPassValidator,
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
            $encoders = [new JsonEncoder()];
            $normalizers = [new ObjectNormalizer()];
            $serializer = new Serializer($normalizers, $encoders);

            $user = $serializer->deserialize($request->getContent(), User::class, 'json');

            $this->userLoginValidator->validate($user->getLogin());
            $this->userPhoneValidator->validate($user->getPhone());
            $this->userPassValidator->validate($user->getPass());

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return new UserData(
                id: $user->getId()
            );
        } catch (ConstraintDefinitionException) {
            return new Response(content: 'Validation error. Please check the input data and try again.', status: Response::HTTP_BAD_REQUEST);
        } catch (DBALException\UniqueConstraintViolationException) {
            return new Response(content: 'The user with the same login already exists. Please fix login and try again.', status: Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (DBALException) {
            return new Response(content: 'Something went wrong follow data saving. Please try again later.', status: Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Throwable) {
            return new Response(content: 'Something went wrong. Please contact the support service.', status: Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}