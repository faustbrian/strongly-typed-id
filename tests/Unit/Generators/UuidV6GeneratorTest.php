<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\StronglyTypedId\Generators\UuidV6Generator;
use Ramsey\Uuid\Uuid;

describe('UuidV6Generator', function (): void {
    describe('Happy Paths', function (): void {
        test('generates valid uuid v6 string', function (): void {
            // Arrange
            $generator = new UuidV6Generator();

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect(Uuid::isValid($id))->toBeTrue();
        });

        test('generates lowercase uuid', function (): void {
            // Arrange
            $generator = new UuidV6Generator();

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBe(mb_strtolower($id));
        });

        test('generates unique ids on each call', function (): void {
            // Arrange
            $generator = new UuidV6Generator();

            // Act
            $id1 = $generator->generate();
            $id2 = $generator->generate();

            // Assert
            expect($id1)->not->toBe($id2);
        });

        test('generates time-sortable uuids', function (): void {
            // Arrange
            $generator = new UuidV6Generator();

            // Act
            $id1 = $generator->generate();
            usleep(1_000); // Wait 1ms to ensure different timestamp
            $id2 = $generator->generate();

            // Assert - UUID v6 is time-sortable (reordered v1)
            expect($id1 < $id2)->toBeTrue();
        });
    });

    describe('Edge Cases', function (): void {
        test('generates many unique ids rapidly', function (): void {
            // Arrange
            $generator = new UuidV6Generator();
            $ids = [];

            // Act
            for ($i = 0; $i < 1_000; ++$i) {
                $ids[] = $generator->generate();
            }

            // Assert
            expect(count(array_unique($ids)))->toBe(1_000);
        });

        test('maintains lowercase consistency across multiple calls', function (): void {
            // Arrange
            $generator = new UuidV6Generator();

            // Act & Assert
            for ($i = 0; $i < 100; ++$i) {
                $id = $generator->generate();
                expect($id)->toBe(mb_strtolower($id));
            }
        });

        test('maintains time ordering under high load', function (): void {
            // Arrange
            $generator = new UuidV6Generator();
            $ids = [];

            // Act
            for ($i = 0; $i < 100; ++$i) {
                $ids[] = $generator->generate();
            }

            // Assert - All IDs should be in chronological order
            $sortedIds = $ids;
            sort($sortedIds);
            expect($ids)->toBe($sortedIds);
        });
    });
});
