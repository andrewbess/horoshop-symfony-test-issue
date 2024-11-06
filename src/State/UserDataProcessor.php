<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\State\UserDataProcessor\CreateProcessor as CreateUserProcessor;
use App\State\UserDataProcessor\DeleteProcessor;
use App\State\UserDataProcessor\UpdateProcessor as UpdateUserProcessor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * The common user data processor that delegate action for the necessary processor (CREATE, UPDATE, DELETE)
 */
class UserDataProcessor implements ProcessorInterface
{
    private const AVAILABLE_OPERATIONS = [
        Request::METHOD_POST,
        Request::METHOD_PUT,
        Request::METHOD_DELETE
    ];

    /**
     * @param UpdateUserProcessor $updateUserProcessor
     * @param CreateUserProcessor $createUserProcessor
     * @param DeleteProcessor $deleteUserProcessor
     */
    public function __construct(
        private readonly UpdateUserProcessor $updateUserProcessor,
        private readonly CreateUserProcessor $createUserProcessor,
        private readonly DeleteProcessor $deleteUserProcessor,
    ) {
    }

    /**
     * The processor executor
     *
     * @param mixed $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return object
     */
    #[\ReturnTypeWillChange]
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): object
    {
        try {
            $request = $this->getRequest($context);

            match ($request->getMethod()) {
                Request::METHOD_PUT => $result = $this->createUserProcessor->process($request),
                Request::METHOD_POST => $result = $this->updateUserProcessor->process($request),
                Request::METHOD_DELETE => $result = $this->deleteUserProcessor->process($request),
            };

            return $result;
        } catch (BadRequestHttpException) {
            return new Response(content: 'Bad request. Please check your request and try again.', status: Response::HTTP_BAD_REQUEST);
        }
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

        if ($request === null || !in_array($request->getMethod(), self::AVAILABLE_OPERATIONS, true)) {
            throw new BadRequestHttpException(message: 'Bad request. Please check the input data and try again.');
        }

        return $request;
    }
}
