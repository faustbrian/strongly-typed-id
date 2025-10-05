<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\StronglyTypedId\Generators\UuidV3Generator;
use Ramsey\Uuid\Uuid;

describe('UuidV3Generator', function (): void {
    describe('Happy Paths', function (): void {
        test('generates valid uuid v3 string with default namespace', function (): void {
            // Arrange
            $generator = new UuidV3Generator();

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect(Uuid::isValid($id))->toBeTrue();
        });

        test('generates lowercase uuid', function (): void {
            // Arrange
            $generator = new UuidV3Generator();

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBe(mb_strtolower($id));
        });

        test('generates unique ids when using random names', function (): void {
            // Arrange
            $generator = new UuidV3Generator();

            // Act
            $id1 = $generator->generate();
            $id2 = $generator->generate();

            // Assert
            expect($id1)->not->toBe($id2);
        });

        test('generates deterministic uuid with fixed name', function (): void {
            // Arrange
            $generator = new UuidV3Generator(Uuid::NAMESPACE_DNS, 'example.com');

            // Act
            $id1 = $generator->generate();
            $id2 = $generator->generate();

            // Assert - Same namespace + name = same UUID
            expect($id1)->toBe($id2);
        });

        test('generates valid uuid with custom namespace', function (): void {
            // Arrange
            $customNamespace = Uuid::NAMESPACE_URL;
            $generator = new UuidV3Generator($customNamespace, 'https://example.com');

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect(Uuid::isValid($id))->toBeTrue();
        });

        test('different namespaces produce different uuids for same name', function (): void {
            // Arrange
            $generator1 = new UuidV3Generator(Uuid::NAMESPACE_DNS, 'example.com');
            $generator2 = new UuidV3Generator(Uuid::NAMESPACE_URL, 'example.com');

            // Act
            $id1 = $generator1->generate();
            $id2 = $generator2->generate();

            // Assert
            expect($id1)->not->toBe($id2);
        });

        test('different names produce different uuids for same namespace', function (): void {
            // Arrange
            $generator1 = new UuidV3Generator(Uuid::NAMESPACE_DNS, 'example.com');
            $generator2 = new UuidV3Generator(Uuid::NAMESPACE_DNS, 'different.com');

            // Act
            $id1 = $generator1->generate();
            $id2 = $generator2->generate();

            // Assert
            expect($id1)->not->toBe($id2);
        });
    });

    describe('Edge Cases', function (): void {
        test('generates consistent uuid with empty string name', function (): void {
            // Arrange
            $generator = new UuidV3Generator(Uuid::NAMESPACE_DNS, '');

            // Act
            $id1 = $generator->generate();
            $id2 = $generator->generate();

            // Assert
            expect($id1)->toBe($id2);
        });

        test('handles unicode characters in name', function (): void {
            // Arrange
            $generator = new UuidV3Generator(Uuid::NAMESPACE_DNS, '你好世界');

            // Act
            $id = $generator->generate();

            // Assert
            expect($id)->toBeString();
            expect(Uuid::isValid($id))->toBeTrue();
        });

        test('maintains lowercase consistency across multiple calls', function (): void {
            // Arrange
            $generator = new UuidV3Generator();

            // Act & Assert
            for ($i = 0; $i < 100; $i++) {
                $id = $generator->generate();
                expect($id)->toBe(mb_strtolower($id));
            }
        });
    });
});
