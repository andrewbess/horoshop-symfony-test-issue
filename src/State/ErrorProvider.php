<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\HttpFoundation\Response;

/**
 * The error provider that handles errors and retrieves the necessary result to avoid unnecessary errors
 */
#[AsAlias('api_platform.state.error_provider')]
#[AsTaggedItem('api_platform.state.error_provider')]
class ErrorProvider implements ProviderInterface
{
    /**
     * The provider executor
     *
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return object
     */
    #[\ReturnTypeWillChange]
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object
    {
        $request = $context['request'];
        $exception = $request->attributes->get('exception');
        $status = $operation->getStatus();

        return match ($status) {
            Response::HTTP_BAD_REQUEST, Response::HTTP_NOT_FOUND, Response::HTTP_UNPROCESSABLE_ENTITY, Response::HTTP_INTERNAL_SERVER_ERROR => new Response(content: $exception->getMessage(), status: $status),
            Response::HTTP_FORBIDDEN => new Response(content: 'Access denied. You have no permissions for this operation.', status: $status),
            default => new Response(content: 'Something went wrong during the request processing.', status: Response::HTTP_INTERNAL_SERVER_ERROR),
        };
    }
}
