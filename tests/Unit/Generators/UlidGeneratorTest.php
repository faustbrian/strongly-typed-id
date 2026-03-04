<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\StronglyTypedId\Generators\UlidGenerator;
use Illuminate\Support\Sleep;
use Illuminate\Support\Str;

describe('UlidGenerator', function (): void {
    describe('Happy Paths', function (): void {
        test('generates valid ulid string', function (): void {
            // Arrange
            $generator = new UlidGenerator();

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect(Str::isUlid($id))->toBeTrue();
        });

        test('generates lowercase ulid', function (): void {
            // Arrange
            $generator = new UlidGenerator();

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBe(mb_strtolower($id));
        });

        test('generates unique ids on each call', function (): void {
            // Arrange
            $generator = new UlidGenerator();

            // Act
            $id1 = $generator->generate();
            $id2 = $generator->generate();

            // Assert
            expect($id1)->not->toBe($id2);
        });

        test('generates lexicographically sortable ulids', function (): void {
            // Arrange
            $generator = new UlidGenerator();

            // Act
            $id1 = $generator->generate();
            Sleep::usleep(1_000); // Wait 1ms to ensure different timestamp
            $id2 = $generator->generate();

            // Assert - ULID is lexicographically sortable
            expect($id1 < $id2)->toBeTrue();
        });

        test('generates 26 character ulid', function (): void {
            // Arrange
            $generator = new UlidGenerator();

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(26);
        });
    });

    describe('Edge Cases', function (): void {
        test('generates many unique ids rapidly', function (): void {
            // Arrange
            $generator = new UlidGenerator();
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
            $generator = new UlidGenerator();

            // Act & Assert
            for ($i = 0; $i < 100; ++$i) {
                $id = $generator->generate();
                expect($id)->toBe(mb_strtolower($id));
            }
        });

        test('ulid length is consistent', function (): void {
            // Arrange
            $generator = new UlidGenerator();

            // Act & Assert
            for ($i = 0; $i < 100; ++$i) {
                $id = $generator->generate();
                expect(mb_strlen($id))->toBe(26);
            }
        });
    });
});
