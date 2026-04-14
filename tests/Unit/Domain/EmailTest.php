<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WeatherFlow\Domain\ValueObject\Email;

final class EmailTest extends TestCase
{
    public function test_empty_string_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Email cannot be empty.');

        new Email('');
    }

    public function test_whitespace_only_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Email cannot be empty.');

        new Email('   ');
    }

    public function test_invalid_format_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format.');

        new Email('not-an-email');
    }

    public function test_trims_and_stores_valid_email(): void
    {
        $email = new Email('  Ada@Example.COM  ');

        $this->assertSame('Ada@Example.COM', $email->value);
    }
}
