<?php

namespace App\Domain\OrderAccountingExport\ValueObject;

use App\Domain\OrderAccountingExport\Enum\ExportAttemptStatus;

final readonly class ExportResult
{
    public function __construct(
        private ExportAttemptStatus $status,
        private ?string $externalReference = null,
        private ?string $errorMessage = null,
    ) {}

    public static function success(?string $externalReference = null): self
    {
        return new self(
            status: ExportAttemptStatus::Success,
            externalReference: $externalReference,
        );
    }

    public static function failed(string $errorMessage): self
    {
        return new self(
            status: ExportAttemptStatus::Failed,
            errorMessage: $errorMessage,
        );
    }

    public function status(): ExportAttemptStatus
    {
        return $this->status;
    }

    public function externalReference(): ?string
    {
        return $this->externalReference;
    }

    public function errorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function isSuccess(): bool
    {
        return $this->status === ExportAttemptStatus::Success;
    }
}
