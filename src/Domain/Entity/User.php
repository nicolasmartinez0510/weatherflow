<?php

declare(strict_types=1);

namespace WeatherFlow\Domain\Entity;

use InvalidArgumentException;
use WeatherFlow\Domain\ValueObject\Email;
use WeatherFlow\Domain\ValueObject\UserId;
use WeatherFlow\Domain\ValueObject\WeatherStationId;

final class User implements WeatherflowEntity
{
    /**
     * @param  list<WeatherStationId>  $subscribedWeatherStationIds
     */
    public function __construct(
        private readonly UserId $id,
        private Email           $email,
        private string          $name,
        private array           $subscribedWeatherStationIds = [],
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
     * @return list<WeatherStationId>
     */
    public function subscribedWeatherStationIds(): array
    {
        return $this->subscribedWeatherStationIds;
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

    public function subscribeToWeatherStation(WeatherStationId $weatherStationId): void
    {
        foreach ($this->subscribedWeatherStationIds as $existing) {
            if ($existing->value === $weatherStationId->value) {
                return;
            }
        }
        $this->subscribedWeatherStationIds[] = $weatherStationId;
    }

    public function unsubscribeFromWeatherStation(WeatherStationId $weatherStationId): void
    {
        $this->subscribedWeatherStationIds = array_values(array_filter(
            $this->subscribedWeatherStationIds,
            static fn (WeatherStationId $id): bool => $id->value !== $weatherStationId->value,
        ));
    }
}
