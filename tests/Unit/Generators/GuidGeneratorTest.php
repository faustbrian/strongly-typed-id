<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\StronglyTypedId\Generators\GuidGenerator;
use Ramsey\Uuid\Uuid;

describe('GuidGenerator', function (): void {
    describe('Happy Paths', function (): void {
        test('generates valid guid string', function (): void {
            // Arrange
            $generator = new GuidGenerator();

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect(Uuid::isValid($id))->toBeTrue();
        });

        test('generates uppercase guid', function (): void {
            // Arrange
            $generator = new GuidGenerator();

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBe(mb_strtoupper($id));
        });

        test('generates unique ids on each call', function (): void {
            // Arrange
            $generator = new GuidGenerator();

            // Act
            $id1 = $generator->generate();
            $id2 = $generator->generate();

            // Assert
            expect($id1)->not->toBe($id2);
        });

        test('generates 36 character guid with hyphens', function (): void {
            // Arrange
            $generator = new GuidGenerator();

            // Act
            $id = $generator->generate();

            // Assert
            expect(mb_strlen($id))->toBe(36);
            expect($id)->toMatch('/^[0-9A-F]{8}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{12}$/');
        });

        test('generates random non-sequential guids', function (): void {
            // Arrange
            $generator = new GuidGenerator();

            // Act
            $id1 = $generator->generate();
            $id2 = $generator->generate();

            // Assert - GUID is random, not guaranteed sortable
            expect($id1)->not->toBe($id2);
            // We cannot assert ordering as GUID v4 is random
        });

        test('generates valid uuid v4 format', function (): void {
            // Arrange
            $generator = new GuidGenerator();

            // Act
            $id = $generator->generate();
            $uuid = Uuid::fromString($id);

            // Assert - Verify UUID version 4 and variant
            expect($uuid->getVersion())->toBe(4);
            expect($uuid->getVariant())->toBeGreaterThanOrEqual(2); // RFC 4122 variant
        });
    });

    describe('Edge Cases', function (): void {
        test('generates many unique ids rapidly', function (): void {
            // Arrange
            $generator = new GuidGenerator();
            $ids = [];

            // Act
            for ($i = 0; $i < 1_000; ++$i) {
                $ids[] = $generator->generate();
            }

            // Assert
            expect(count(array_unique($ids)))->toBe(1_000);
        });

        test('maintains uppercase consistency across multiple calls', function (): void {
            // Arrange
            $generator = new GuidGenerator();

            // Act & Assert
            for ($i = 0; $i < 100; ++$i) {
                $id = $generator->generate();
                expect($id)->toBe(mb_strtoupper($id));
            }
        });

        test('guid length is consistent', function (): void {
            // Arrange
            $generator = new GuidGenerator();

            // Act & Assert
            for ($i = 0; $i < 100; ++$i) {
                $id = $generator->generate();
                expect(mb_strlen($id))->toBe(36);
            }
        });

        test('guid format is consistent with hyphen positions', function (): void {
            // Arrange
            $generator = new GuidGenerator();

            // Act & Assert
            for ($i = 0; $i < 100; ++$i) {
                $id = $generator->generate();
                expect($id[8])->toBe('-');
                expect($id[13])->toBe('-');
                expect($id[18])->toBe('-');
                expect($id[23])->toBe('-');
            }
        });

        test('all hex characters are uppercase', function (): void {
            // Arrange
            $generator = new GuidGenerator();

            // Act & Assert
            for ($i = 0; $i < 100; ++$i) {
                $id = $generator->generate();
                $hexOnly = str_replace('-', '', $id);
                expect($hexOnly)->toMatch('/^[0-9A-F]+$/');
                expect($hexOnly)->not->toMatch('/[a-f]/');
            }
        });

        test('version bits are correct for uuid v4', function (): void {
            // Arrange
            $generator = new GuidGenerator();

            // Act & Assert
            for ($i = 0; $i < 50; ++$i) {
                $id = $generator->generate();
                // Version 4 means character at position 14 should be '4'
                expect($id[14])->toBe('4');
                // Variant bits: character at position 19 should be 8, 9, A, or B
                expect($id[19])->toBeIn(['8', '9', 'A', 'B']);
            }
        });
    });
});
