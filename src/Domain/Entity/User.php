<?php

declare(strict_types=1);

namespace WeatherFlow\Domain\Entity;

use InvalidArgumentException;
use WeatherFlow\Domain\ValueObject\Email;
use WeatherFlow\Domain\ValueObject\StationId;
use WeatherFlow\Domain\ValueObject\UserId;

final class User implements WeatherflowEntity
{
    /**
     * @param  list<StationId>  $subscribedStationIds
     */
    public function __construct(
        private readonly UserId $id,
        private Email           $email,
        private string          $name,
        private array           $subscribedStationIds = [],
    ) {
        if ($name === '') {
            throw new InvalidArgumentException('User name cannot be empty.');
        }
    }

    public function id(): UserId
    {
        return $this->id;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return list<StationId>
     */
    public function subscribedStationIds(): array
    {
        return $this->subscribedStationIds;
    }

    public function rename(string $name): void
    {
        if ($name === '') {
            throw new InvalidArgumentException('User name cannot be empty.');
        }
        $this->name = $name;
    }

    public function changeEmail(Email $email): void
    {
        $this->email = $email;
    }

    public function subscribeToStation(StationId $stationId): void
    {
        foreach ($this->subscribedStationIds as $existing) {
            if ($existing->value === $stationId->value) {
                return;
            }
        }
        $this->subscribedStationIds[] = $stationId;
    }

    public function unsubscribeFromStation(StationId $stationId): void
    {
        $this->subscribedStationIds = array_values(array_filter(
            $this->subscribedStationIds,
            static fn (StationId $id): bool => $id->value !== $stationId->value,
        ));
    }
}
