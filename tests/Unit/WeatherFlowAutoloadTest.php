<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use WeatherFlow\Shared\SkeletonMarker;

final class WeatherFlowAutoloadTest extends TestCase
{
    public function test_psr4_autoload_instantiates_src_class(): void
    {
        $this->assertInstanceOf(SkeletonMarker::class, new SkeletonMarker());
    }
}
