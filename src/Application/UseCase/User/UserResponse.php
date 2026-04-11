<?php

declare(strict_types=1);

namespace WeatherFlow\Application\UseCase\User;

use WeatherFlow\Domain\Entity\User;
use WeatherFlow\Domain\ValueObject\StationId;

final readonly class UserResponse
{
    /**
     * @param  list<string>  $subscribedStationIds
     */
    public function __construct(
        public string $id,
        public string $email,
        public string $name,
        public array $subscribedStationIds,
    ) {
    }

    public static function fromEntity(User $user): self
    {
        return new self(
            $user->id()->value,
            $user->email()->value,
            $user->name(),
            array_map(static fn (StationId $id): string => $id->value, $user->subscribedStationIds()),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'subscribed_station_ids' => $this->subscribedStationIds,
        ];
    }
}
