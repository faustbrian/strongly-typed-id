<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\StronglyTypedId\Contracts\IdGeneratorInterface;
use Cline\StronglyTypedId\Facades\IdGenerator as IdGeneratorFacade;
use Ramsey\Uuid\Uuid;
use Tests\Fixtures\UserId;

describe('Custom Generator Implementation', function (): void {
    describe('Happy Paths', function (): void {
        test('accepts custom generator class implementing IdGenerator contract', function (): void {
            // Arrange - Create custom generator
            $customGenerator = new class() implements IdGeneratorInterface
            {
                public function generate(): string
                {
                    return 'custom-'.Uuid::uuid4()->toString();
                }
            };

            app()->instance(IdGeneratorInterface::class, $customGenerator);

            // Act
            $id = IdGeneratorFacade::generate();

            // Assert
            expect($id)->toStartWith('custom-');
        });

        test('custom generator works with StronglyTypedId when generating valid UUIDs', function (): void {
            // Arrange - Custom generator that produces valid UUIDs
            $customGenerator = new class() implements IdGeneratorInterface
            {
                public function generate(): string
                {
                    return mb_strtolower(Uuid::uuid4()->toString());
                }
            };

            app()->instance(IdGeneratorInterface::class, $customGenerator);

            // Act
            $userId = UserId::generate();

            // Assert
            expect($userId)->toBeInstanceOf(UserId::class);
            expect(Uuid::isValid($userId->value))->toBeTrue();
        });
    });

    describe('Sad Paths', function (): void {
        test('StronglyTypedId validation fails with invalid custom generator output', function (): void {
            // Arrange - Custom generator producing invalid UUIDs
            $customGenerator = new class() implements IdGeneratorInterface
            {
                public function generate(): string
                {
                    return 'not-a-valid-uuid';
                }
            };

            app()->instance(IdGeneratorInterface::class, $customGenerator);

            // Act & Assert
            expect(fn (): UserId => UserId::generate())
                ->toThrow(InvalidArgumentException::class, 'Invalid UUID format');
        });

        test('StronglyTypedId validation fails with empty custom generator output', function (): void {
            // Arrange - Custom generator returning empty string
            $customGenerator = new class() implements IdGeneratorInterface
            {
                public function generate(): string
                {
                    return '';
                }
            };

            app()->instance(IdGeneratorInterface::class, $customGenerator);

            // Act & Assert
            expect(fn (): UserId => UserId::generate())
                ->toThrow(InvalidArgumentException::class, 'cannot be empty');
        });
    });
});
