<?php

declare(strict_types=1);

namespace WeatherFlow\Domain\Entity;

use InvalidArgumentException;
use WeatherFlow\Domain\ValueObject\Coordinates;
use WeatherFlow\Domain\ValueObject\StationId;
use WeatherFlow\Domain\ValueObject\StationStatus;
use WeatherFlow\Domain\ValueObject\UserId;

final class WeatherStation implements WeatherflowEntity
{
    public function __construct(
        private readonly StationId $id,
        private string $name,
        private Coordinates $coordinates,
        private string $sensorModel,
        private StationStatus $status,
        private readonly UserId $ownerId,
    ) {
        if ($name === '') {
            throw new InvalidArgumentException('Station name cannot be empty.');
        }
        if ($sensorModel === '') {
            throw new InvalidArgumentException('Sensor model cannot be empty.');
        }
    }

    public function id(): StationId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function coordinates(): Coordinates
    {
        return $this->coordinates;
    }

    public function sensorModel(): string
    {
        return $this->sensorModel;
    }

    public function status(): StationStatus
    {
        return $this->status;
    }

    public function ownerId(): UserId
    {
        return $this->ownerId;
    }

    public function rename(string $name): void
    {
        if ($name === '') {
            throw new InvalidArgumentException('Station name cannot be empty.');
        }
        $this->name = $name;
    }

    public function relocate(Coordinates $coordinates): void
    {
        $this->coordinates = $coordinates;
    }

    public function changeSensorModel(string $sensorModel): void
    {
        if ($sensorModel === '') {
            throw new InvalidArgumentException('Sensor model cannot be empty.');
        }
        $this->sensorModel = $sensorModel;
    }

    public function setStatus(StationStatus $status): void
    {
        $this->status = $status;
    }
}
