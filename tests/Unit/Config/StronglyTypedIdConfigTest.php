<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\StronglyTypedId\Contracts\IdGeneratorInterface;
use Cline\StronglyTypedId\Enums\GeneratorType;
use Cline\StronglyTypedId\Facades\IdGenerator as IdGeneratorFacade;
use Cline\StronglyTypedId\Generators\UlidGenerator;
use Cline\StronglyTypedId\Generators\UuidV4Generator;
use Cline\StronglyTypedId\Generators\UuidV7Generator;
use Illuminate\Support\Facades\Config;
use Ramsey\Uuid\Uuid;
use Tests\Fixtures\UserId;

describe('IdGenerator Configuration', function (): void {
    describe('Happy Paths', function (): void {
        test('uses uuid_v7 generator by default', function (): void {
            // Arrange
            Config::set('strongly-typed-id.generator', GeneratorType::UuidV7->value);

            // Act
            $generator = app(IdGeneratorInterface::class);

            // Assert
            expect($generator)->toBeInstanceOf(UuidV7Generator::class);
        });

        test('binds uuid_v4 generator when configured', function (): void {
            // Arrange
            Config::set('strongly-typed-id.generator', GeneratorType::UuidV4->value);
            app()->forgetInstance(IdGeneratorInterface::class);

            // Act
            $generator = app(IdGeneratorInterface::class);

            // Assert
            expect($generator)->toBeInstanceOf(UuidV4Generator::class);
        });

        test('binds ulid generator when configured', function (): void {
            // Arrange
            Config::set('strongly-typed-id.generator', GeneratorType::Ulid->value);
            app()->forgetInstance(IdGeneratorInterface::class);

            // Act
            $generator = app(IdGeneratorInterface::class);

            // Assert
            expect($generator)->toBeInstanceOf(UlidGenerator::class);
        });

        test('facade resolves configured generator', function (): void {
            // Arrange
            Config::set('strongly-typed-id.generator', GeneratorType::UuidV7->value);
            app()->forgetInstance(IdGeneratorInterface::class);

            // Act
            $id = IdGeneratorFacade::generate();

            // Assert
            expect($id)->toBeString();
            expect(Uuid::isValid($id))->toBeTrue();
        });

        test('StronglyTypedId uses configured generator', function (): void {
            // Arrange
            Config::set('strongly-typed-id.generator', GeneratorType::UuidV4->value);
            app()->forgetInstance(IdGeneratorInterface::class);

            // Act
            $userId = UserId::generate();

            // Assert
            expect($userId->value)->toBeString();
            expect(Uuid::isValid($userId->value))->toBeTrue();
        });
    });

    describe('Sad Paths', function (): void {
        test('throws exception for invalid generator configuration', function (): void {
            // Arrange
            Config::set('strongly-typed-id.generator', 'invalid_generator');
            app()->forgetInstance(IdGeneratorInterface::class);

            // Act & Assert
            expect(fn () => app(IdGeneratorInterface::class))
                ->toThrow(InvalidArgumentException::class, 'Unsupported generator type "invalid_generator"');
        });

        test('throws exception for empty generator configuration', function (): void {
            // Arrange
            Config::set('strongly-typed-id.generator', '');
            app()->forgetInstance(IdGeneratorInterface::class);

            // Act & Assert
            expect(fn () => app(IdGeneratorInterface::class))
                ->toThrow(InvalidArgumentException::class);
        });

        test('throws exception for null generator configuration', function (): void {
            // Arrange
            Config::set('strongly-typed-id.generator', null);
            app()->forgetInstance(IdGeneratorInterface::class);

            // Act & Assert
            expect(fn () => app(IdGeneratorInterface::class))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('Edge Cases', function (): void {
        test('handles generator configuration changes after facade clear', function (): void {
            // Arrange - First request with uuid_v7
            Config::set('strongly-typed-id.generator', GeneratorType::UuidV7->value);
            app()->forgetInstance(IdGeneratorInterface::class);
            IdGeneratorFacade::clearResolvedInstance(IdGeneratorInterface::class);
            $generator1 = app(IdGeneratorInterface::class);

            // Act - Change to ulid and clear facade
            Config::set('strongly-typed-id.generator', GeneratorType::Ulid->value);
            app()->forgetInstance(IdGeneratorInterface::class);
            IdGeneratorFacade::clearResolvedInstance(IdGeneratorInterface::class);
            $generator2 = app(IdGeneratorInterface::class);

            // Assert - Different generator classes bound
            expect($generator1)->toBeInstanceOf(UuidV7Generator::class);
            expect($generator2)->toBeInstanceOf(UlidGenerator::class);
        });

        test('generator binding is case-sensitive', function (): void {
            // Arrange
            Config::set('strongly-typed-id.generator', 'UUID_V7');
            app()->forgetInstance(IdGeneratorInterface::class);

            // Act & Assert
            expect(fn () => app(IdGeneratorInterface::class))
                ->toThrow(InvalidArgumentException::class);
        });
    });
});
