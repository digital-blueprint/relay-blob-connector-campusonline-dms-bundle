<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity;

use ApiPlatform\Metadata\Error as ApiPlatformError;
use ApiPlatform\Metadata\ErrorResource;
use ApiPlatform\Metadata\Exception\ProblemExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Serializer;

#[ErrorResource(
    uriTemplate: '/errors/{status}',
    types: ['hydra:Error'],
    operations: [
        new ApiPlatformError(
            outputFormats: ['json' => ['application/problem+json']],
            routeName: 'api_errors',
            normalizationContext: [
                'groups' => ['jsonproblem'],
                'skip_null_values' => true,
                'rfc_7807_compliant_errors' => true,
            ],
            name: '_api_errors_problem',
        ),
    ],
    uriVariables: ['status'],
    openapi: false,
    graphQlOperations: [],
    provider: 'api_platform.state.error_provider'
)]
class Error extends \ApiPlatform\State\ApiResource\Error implements ProblemExceptionInterface
{
    #[Groups(['json', 'jsonproblem'])]
    #[Context([Serializer::EMPTY_ARRAY_AS_OBJECT => true])]
    private ?\ArrayObject $diagnosticContext;

    public function __construct(int $statusCode, string $message = '', ?string $errorCode = null, ?string $resourceUid = null, ?string $errorDetail = null)
    {
        parent::__construct(Response::$statusTexts[$statusCode] ?? 'An error occurred', $message, $statusCode);

        $diagnosticContext = [];
        if ($errorCode !== null) {
            $diagnosticContext['ERROR_CODE'] = $errorCode;
        }
        if ($resourceUid !== null) {
            $diagnosticContext['RESOURCE_UID'] = $resourceUid;
        }
        if ($errorDetail !== null) {
            $diagnosticContext['ERROR_DETAIL'] = $errorDetail;
        }
        $this->diagnosticContext = $diagnosticContext !== [] ? new \ArrayObject($diagnosticContext) : null;
    }

    public function getDiagnosticContext(): ?\ArrayObject
    {
        return $this->diagnosticContext;
    }
}
