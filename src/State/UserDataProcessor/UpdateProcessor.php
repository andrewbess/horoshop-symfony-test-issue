<?php

namespace App\State\UserDataProcessor;

use App\Dto\User\UserData;
use App\Entity\User;
use App\Validator\User\IdValidator;
use App\Validator\User\LoginValidator;
use App\Validator\User\PassValidator;
use App\Validator\User\PhoneValidator;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Throwable;

/**
 * The user data processor to update existing user by data provided in the request
 */
class UpdateProcessor
{
    /**
     * @param IdValidator $userIdValidator
     * @param LoginValidator $userLoginValidator
     * @param PhoneValidator $userPhoneValidator
     * @param PassValidator $userPassValidator
     * @param UserPasswordHasherInterface $passwordHasher
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        private readonly IdValidator $userIdValidator,
        private readonly LoginValidator $userLoginValidator,
        private readonly PhoneValidator $userPhoneValidator,
        private readonly PassValidator $userPassValidator,
        private readonly UserPasswordHasherInterface $passwordHasher,
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

            $userData = $serializer->deserialize($request->getContent(), UserData::class, 'json');
            $userId = $userData->id;

            $this->userIdValidator->validate($userId);

            $user = $this->entityManager->getRepository(User::class)->find($userId);

            if ($user === null) {
                throw HttpException::fromStatusCode(statusCode: Response::HTTP_NOT_FOUND, message: 'The user by provided ID does not exist.');
            }

            $this->userLoginValidator->validate($userData->login);
            $this->userPhoneValidator->validate($userData->phone);
            $this->userPassValidator->validate($userData->pass);

            $user->setLogin($userData->login);
            $user->setPhone($userData->phone);

            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $userData->pass
            );
            $user->setPass($hashedPassword);

            $this->entityManager->flush();

            return new UserData(
                login: $user->getLogin(),
                phone: $user->getPhone(),
                pass: 'The password was hashed and will not be able to show.'
            );
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