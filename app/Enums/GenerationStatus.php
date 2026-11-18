<?php

namespace App\Enums;

enum GenerationStatus: string
{
    case Pending = 'pending';
    case Queued = 'queued';
    case Processing = 'processing';
    case Succeeded = 'succeeded';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending, self::Queued, self::Processing => 'In progress',
            self::Succeeded => 'Succeeded',
            self::Failed => 'Failed',
        };
    }

    public function isFinal(): bool
    {
        return $this === self::Succeeded || $this === self::Failed;
    }

    public function canMoveTo(self $nextStatus): bool
    {
        if ($this->isFinal()) {
            return false;
        }

        return $nextStatus->rank() > $this->rank();
    }

    /** @return list<self> */
    public static function unfinished(): array
    {
        return array_values(array_filter(self::cases(), fn (self $status) => ! $status->isFinal()));
    }

    /**
     * The statuses a generation may currently be in for a move to this status to be allowed.
     *
     * @return list<self>
     */
    public function allowedPredecessors(): array
    {
        return array_values(array_filter(self::cases(), fn (self $status) => $status->canMoveTo($this)));
    }

    private function rank(): int
    {
        return match ($this) {
            self::Pending => 0,
            self::Queued => 1,
            self::Processing => 2,
            self::Succeeded, self::Failed => 3,
        };
    }
}
