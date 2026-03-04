<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\StronglyTypedId\Contracts\IdGeneratorInterface;
use Cline\StronglyTypedId\Enums\GeneratorType;
use Cline\StronglyTypedId\Generators\GuidGenerator;
use Cline\StronglyTypedId\Generators\HashidsGenerator;
use Cline\StronglyTypedId\Generators\SqidsGenerator;
use Cline\StronglyTypedId\Generators\UlidGenerator;
use Cline\StronglyTypedId\Generators\UuidV7Generator;
use Illuminate\Support\Facades\Config;

describe('Service Provider Binding', function (): void {
    describe('Happy Paths', function (): void {
        test('service provider binds generator to container', function (): void {
            // Act
            $bound = app()->bound(IdGeneratorInterface::class);

            // Assert
            expect($bound)->toBeTrue();
        });

        test('service provider resolves generator from container', function (): void {
            // Act
            $generator = resolve(IdGeneratorInterface::class);

            // Assert
            expect($generator)->toBeInstanceOf(IdGeneratorInterface::class);
        });

        test('service provider binding resolves consistently', function (): void {
            // Act
            $generator1 = resolve(IdGeneratorInterface::class);
            $generator2 = resolve(IdGeneratorInterface::class);

            // Assert - Both instances should be of the same class
            expect($generator1::class)->toBe($generator2::class);
        });
    });

    describe('Edge Cases', function (): void {
        test('rebinding generator after configuration change works', function (): void {
            // Arrange
            Config::set('strongly-typed-id.generator', GeneratorType::UuidV7->value);
            $generator1 = resolve(IdGeneratorInterface::class);

            // Act - Change configuration and rebind
            Config::set('strongly-typed-id.generator', GeneratorType::Ulid->value);
            app()->forgetInstance(IdGeneratorInterface::class);
            $generator2 = resolve(IdGeneratorInterface::class);

            // Assert
            expect($generator1)->toBeInstanceOf(UuidV7Generator::class);
            expect($generator2)->toBeInstanceOf(UlidGenerator::class);
        });

        test('resolves sqids generator from config', function (): void {
            // Arrange
            Config::set('strongly-typed-id.generator', GeneratorType::Sqids->value);
            app()->forgetInstance(IdGeneratorInterface::class);

            // Act
            $generator = resolve(IdGeneratorInterface::class);

            // Assert
            expect($generator)->toBeInstanceOf(SqidsGenerator::class);
        });

        test('resolves hashids generator from config', function (): void {
            // Arrange
            Config::set('strongly-typed-id.generator', GeneratorType::Hashids->value);
            app()->forgetInstance(IdGeneratorInterface::class);

            // Act
            $generator = resolve(IdGeneratorInterface::class);

            // Assert
            expect($generator)->toBeInstanceOf(HashidsGenerator::class);
        });

        test('resolves guid generator from config', function (): void {
            // Arrange
            Config::set('strongly-typed-id.generator', GeneratorType::Guid->value);
            app()->forgetInstance(IdGeneratorInterface::class);

            // Act
            $generator = resolve(IdGeneratorInterface::class);

            // Assert
            expect($generator)->toBeInstanceOf(GuidGenerator::class);
        });
    });
});
