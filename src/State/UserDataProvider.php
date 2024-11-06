<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\State\UserDataProvider\GetDataByContentBodyProvider;
use App\State\UserDataProvider\GetDataByQueryProvider;
use Symfony\Component\HttpFoundation\Request;

/**
 * The common user data provider that delegate getting user for the necessary provider (get-by-query-param, get-by-request-body)
 */
class UserDataProvider implements ProviderInterface
{
    /**
     * @param GetDataByQueryProvider $getDataByQueryProvider
     * @param GetDataByContentBodyProvider $getDataByContentBodyProvider
     */
    public function __construct(
        private readonly GetDataByQueryProvider $getDataByQueryProvider,
        private readonly GetDataByContentBodyProvider $getDataByContentBodyProvider
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
    #[\ReturnTypeWillChange]
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        match ($operation->getMethod()) {
            Request::METHOD_GET => $result = $this->getDataByQueryProvider->provide($operation, $uriVariables, $context),
            Request::METHOD_POST => $result = $this->getDataByContentBodyProvider->provide($operation, $uriVariables, $context),
        };

        return $result;
    }
}
