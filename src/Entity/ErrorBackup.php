<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity;

// use ApiPlatform\Metadata\ErrorResource;
use ApiPlatform\Metadata\Exception\ProblemExceptionInterface;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Serializer\Annotation\Groups;

// #[ErrorResource]
class ErrorBackup extends \Exception implements ProblemExceptionInterface, HttpExceptionInterface
{
    #[Groups(['jsonld', 'jsonproblem'])]
    private string $type = '';

    #[Groups(['jsonld', 'jsonproblem'])]
    private ?string $title = null;

    #[Groups(['jsonld', 'jsonproblem'])]
    private ?string $detail = null;

    #[Groups(['jsonld', 'jsonproblem'])]
    private ?string $stackTrace = null;

    #[Groups(['jsonld', 'jsonproblem'])]
    private ?string $instance = null;

    #[Groups(['jsonld', 'jsonproblem'])]
    private ?array $diagnosticContext = null;

    #[Groups(['jsonld', 'jsonproblem'])]
    private ?int $status = null;

    //    public function __construct(int $statusCode, ?string $message = '', ?\Throwable $previous = null, array $headers = [], ?int $code = 0)
    //    {
    //        parent::__construct($statusCode, $message, $previous, $headers, $code);
    //    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getDetail(): ?string
    {
        return $this->detail;
    }

    public function setDetail(?string $detail): void
    {
        $this->detail = $detail;
    }

    public function getStackTrace(): ?string
    {
        return $this->stackTrace;
    }

    public function setStackTrace(?string $stackTrace): void
    {
        $this->stackTrace = $stackTrace;
    }

    public function getInstance(): ?string
    {
        return $this->instance;
    }

    public function setInstance(?string $instance): void
    {
        $this->instance = $instance;
    }

    public function getDiagnosticContext(): ?array
    {
        return $this->diagnosticContext;
    }

    public function setDiagnosticContext(?array $diagnosticContext): void
    {
        $this->diagnosticContext = $diagnosticContext;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): void
    {
        $this->status = $status;
    }

    public function getStatusCode(): int
    {
        return $this->status;
    }

    public function getHeaders(): array
    {
        return [];
    }
}
