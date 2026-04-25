<?php

declare(strict_types=1);

namespace WeatherFlow\Application\UseCase\User;

use WeatherFlow\Domain\Entity\User;
use WeatherFlow\Domain\Entity\WeatherflowEntity;
use WeatherFlow\Domain\ValueObject\WeatherStationId;

final readonly class UserResponse
{
    /**
     * @param  list<string>  $subscribedWeatherStationIds
     */
    public function __construct(
        public string $id,
        public string $email,
        public string $name,
        public array $subscribedWeatherStationIds,
    ) {
    }

    public static function fromEntity(User|WeatherflowEntity $user): self
    {
        return new self(
            $user->id()->value,
            $user->email()->value,
            $user->name(),
            array_map(static fn (WeatherStationId $id): string => $id->value, $user->subscribedWeatherStationIds()),
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
            'subscribed_weather_station_ids' => $this->subscribedWeatherStationIds,
        ];
    }
}
