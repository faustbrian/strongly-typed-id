<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\StronglyTypedId\Generators\UuidV8Generator;
use Ramsey\Uuid\Uuid;

describe('UuidV8Generator', function (): void {
    describe('Happy Paths', function (): void {
        test('generates valid uuid v8 string', function (): void {
            // Arrange
            $generator = new UuidV8Generator();

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect(Uuid::isValid($id))->toBeTrue();
        });

        test('generates lowercase uuid', function (): void {
            // Arrange
            $generator = new UuidV8Generator();

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBe(mb_strtolower($id));
        });

        test('generates unique ids on each call', function (): void {
            // Arrange
            $generator = new UuidV8Generator();

            // Act
            $id1 = $generator->generate();
            $id2 = $generator->generate();

            // Assert
            expect($id1)->not->toBe($id2);
        });

        test('generates random non-sequential uuids', function (): void {
            // Arrange
            $generator = new UuidV8Generator();

            // Act
            $id1 = $generator->generate();
            $id2 = $generator->generate();

            // Assert - UUID v8 uses random bytes, not guaranteed sortable
            expect($id1)->not->toBe($id2);
            // We cannot assert ordering with v8 as it uses random bytes
        });
    });

    describe('Edge Cases', function (): void {
        test('generates many unique ids rapidly', function (): void {
            // Arrange
            $generator = new UuidV8Generator();
            $ids = [];

            // Act
            for ($i = 0; $i < 1_000; $i++) {
                $ids[] = $generator->generate();
            }

            // Assert
            expect(count(array_unique($ids)))->toBe(1_000);
        });

        test('maintains lowercase consistency across multiple calls', function (): void {
            // Arrange
            $generator = new UuidV8Generator();

            // Act & Assert
            for ($i = 0; $i < 100; $i++) {
                $id = $generator->generate();
                expect($id)->toBe(mb_strtolower($id));
            }
        });

        test('generates ids with high entropy', function (): void {
            // Arrange
            $generator = new UuidV8Generator();
            $ids = [];

            // Act
            for ($i = 0; $i < 100; $i++) {
                $ids[] = $generator->generate();
            }

            // Assert - Check that IDs are sufficiently random
            // All IDs should be unique (no duplicates)
            expect(count(array_unique($ids)))->toBe(100);

            // IDs should not be in sorted order (random distribution)
            $sortedIds = $ids;
            sort($sortedIds);
            expect($ids)->not->toBe($sortedIds);
        });
    });
});
