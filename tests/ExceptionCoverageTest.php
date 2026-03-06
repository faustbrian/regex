<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Regex\Exceptions\RegexMatchFailed;
use Cline\Regex\Exceptions\RegexReplaceFailed;

it('exposes regex match failure metadata from preg errors', function (): void {
    $exception = RegexMatchFailed::fromPregError(
        '/foo/',
        'short subject',
        'match',
        7,
        'Backtrack limit exhausted',
    );

    expect($exception->pattern())->toBe('/foo/')
        ->and($exception->subject())->toBe('short subject')
        ->and($exception->subjectPreview())->toBe('short subject')
        ->and($exception->operation())->toBe('match')
        ->and($exception->pregErrorCode())->toBe(7)
        ->and($exception->pregMessage())->toBe('Backtrack limit exhausted')
        ->and($exception->getPrevious())->toBeNull()
        ->and($exception->getMessage())
        ->toBe(
            'Regex match failed for pattern `/foo/` on subject `short subject`. '
            .'Backtrack limit exhausted',
        );
});

it('truncates long subjects and preserves previous throwables for match failures', function (): void {
    $previous = new RuntimeException('No ending delimiter');
    $subject = str_repeat('a', 45);

    $exception = RegexMatchFailed::fromThrowable('/foo', $subject, 'match', $previous);

    expect($exception->subject())->toBe($subject)
        ->and($exception->subjectPreview())->toBe(str_repeat('a', 40).'...')
        ->and($exception->pregErrorCode())->toBe(0)
        ->and($exception->pregMessage())->toBe('No ending delimiter')
        ->and($exception->getPrevious())->toBe($previous)
        ->and($exception->getMessage())
        ->toBe(
            'Regex match failed for pattern `/foo` on subject `'
            .str_repeat('a', 40)
            .'...`. No ending delimiter',
        );
});

it('exposes regex replace failure metadata from preg errors', function (): void {
    $exception = RegexReplaceFailed::fromPregError(
        '/bar/',
        'replace subject',
        'replace',
        3,
        'Internal error',
    );

    expect($exception->pattern())->toBe('/bar/')
        ->and($exception->subject())->toBe('replace subject')
        ->and($exception->subjectPreview())->toBe('replace subject')
        ->and($exception->operation())->toBe('replace')
        ->and($exception->pregErrorCode())->toBe(3)
        ->and($exception->pregMessage())->toBe('Internal error')
        ->and($exception->getPrevious())->toBeNull()
        ->and($exception->getMessage())
        ->toBe(
            'Regex replace failed for pattern `/bar/` on subject `replace subject`. '
            .'Internal error',
        );
});

it('wraps throwables for replace failures', function (): void {
    $previous = new RuntimeException('Callback exploded');

    $exception = RegexReplaceFailed::fromThrowable('/bar/', 'subject', 'replace', $previous);

    expect($exception->pregErrorCode())->toBe(0)
        ->and($exception->pregMessage())->toBe('Callback exploded')
        ->and($exception->getPrevious())->toBe($previous)
        ->and($exception->getMessage())
        ->toBe(
            'Regex replace failed for pattern `/bar/` on subject `subject`. '
            .'Callback exploded',
        );
});
