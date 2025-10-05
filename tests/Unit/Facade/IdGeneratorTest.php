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
use Cline\StronglyTypedId\Generators\UuidV7Generator;
use Illuminate\Support\Facades\Config;

describe('Facade Functionality', function (): void {
    describe('Happy Paths', function (): void {
        test('facade resolves to bound generator instance', function (): void {
            // Arrange
            Config::set('strongly-typed-id.generator', GeneratorType::UuidV7->value);
            app()->forgetInstance(IdGeneratorInterface::class);

            // Act
            $generator = IdGeneratorFacade::getFacadeRoot();

            // Assert
            expect($generator)->toBeInstanceOf(UuidV7Generator::class);
        });

        test('facade generate method returns string', function (): void {
            // Arrange
            Config::set('strongly-typed-id.generator', GeneratorType::UuidV7->value);
            app()->forgetInstance(IdGeneratorInterface::class);

            // Act
            $id = IdGeneratorFacade::generate();

            // Assert
            expect($id)->toBeString();
        });

        test('facade works across different generator configurations', function (string $generatorType): void {
            // Arrange
            Config::set('strongly-typed-id.generator', $generatorType);
            app()->forgetInstance(IdGeneratorInterface::class);

            // Act
            $id = IdGeneratorFacade::generate();

            // Assert
            expect($id)->toBeString();
            expect(mb_strlen($id))->toBeGreaterThan(0);
        })->with([
            'uuid_v7' => ['uuid_v7'],
            'uuid_v4' => ['uuid_v4'],
            'ulid' => ['ulid'],
        ]);
    });

    describe('Edge Cases', function (): void {
        test('facade can be called multiple times consecutively', function (): void {
            // Arrange
            Config::set('strongly-typed-id.generator', GeneratorType::UuidV7->value);
            app()->forgetInstance(IdGeneratorInterface::class);

            // Act
            $ids = [];

            for ($i = 0; $i < 10; $i++) {
                $ids[] = IdGeneratorFacade::generate();
            }

            // Assert
            expect(count(array_unique($ids)))->toBe(10);
        });
    });
});
