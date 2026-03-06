<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Regex\Exceptions\RegexReplaceFailed;
use Cline\Regex\MatchResult;
use Cline\Regex\Pattern;
use Cline\Regex\ReplaceResult;

it('replaces matches with a string through the compiled pattern', function (): void {
    $pattern = new Pattern('/a/');
    $result = $pattern->replaceWith('b', 'aabb');

    expect($result)->toBeInstanceOf(ReplaceResult::class)
        ->and($result->pattern())->toBe('/a/')
        ->and($result->originalSubject())->toBe('aabb')
        ->and($result->subject())->toBe('bbbb')
        ->and($result->replacements())->toBe(2);
});

it('replaces matches with a callback through the compiled pattern', function (): void {
    $result = new Pattern('/a(b)/')->replaceUsing(
        fn (MatchResult $match): string => $match->value().':'.$match->offset(),
        'abc',
    );

    expect($result->subject())->toBe('ab:0c')
        ->and($result->replacements())->toBe(1);
});

it('supports replacement limits', function (): void {
    $result = new Pattern('/a/')->replaceWith('b', 'aabb', limit: 1);

    expect($result->subject())->toBe('babb')
        ->and($result->replacements())->toBe(1);
});

it('exposes structured metadata for replacement failures', function (): void {
    try {
        new Pattern('/a')->replaceWith('b', 'aaaa');

        $this->fail('Expected RegexReplaceFailed to be thrown.');
    } catch (RegexReplaceFailed $regexReplaceFailed) {
        expect($regexReplaceFailed->pattern())->toBe('/a')
            ->and($regexReplaceFailed->subject())->toBe('aaaa')
            ->and($regexReplaceFailed->operation())->toBe('replace')
            ->and($regexReplaceFailed->pregMessage())->toContain('No ending delimiter');
    }
});

it('wraps callback throwables as replacement failures', function (): void {
    expect(fn (): ReplaceResult => new Pattern('/a/')->replaceUsing(
        function (): string {
            throw new RuntimeException('boom');
        },
        'aaaa',
    ))->toThrow(RegexReplaceFailed::class, 'boom');
});

it('exposes structured metadata for callback replacement preg failures', function (): void {
    try {
        new Pattern('/a')->replaceUsing(static fn (): string => 'b', 'aaaa');

        $this->fail('Expected RegexReplaceFailed to be thrown.');
    } catch (RegexReplaceFailed $regexReplaceFailed) {
        expect($regexReplaceFailed->pattern())->toBe('/a')
            ->and($regexReplaceFailed->subject())->toBe('aaaa')
            ->and($regexReplaceFailed->operation())->toBe('replace')
            ->and($regexReplaceFailed->pregMessage())->toContain('No ending delimiter');
    }
});

it('exposes structured metadata for preg replace failures', function (): void {
    $previousLimit = ini_get('pcre.backtrack_limit');
    ini_set('pcre.backtrack_limit', '1');

    try {
        new Pattern('/(?:\D+|<\d+>)*[!?]/')->replaceWith('x', 'foobar foobar foobar');

        $this->fail('Expected RegexReplaceFailed to be thrown.');
    } catch (RegexReplaceFailed $regexReplaceFailed) {
        expect($regexReplaceFailed->operation())->toBe('replace')
            ->and($regexReplaceFailed->pregMessage())->toBe('Backtrack limit exhausted')
            ->and($regexReplaceFailed->pregErrorCode())->not->toBe(0);
    } finally {
        ini_set('pcre.backtrack_limit', (string) $previousLimit);
    }
});

it('exposes structured metadata for preg callback replace failures', function (): void {
    $previousLimit = ini_get('pcre.backtrack_limit');
    ini_set('pcre.backtrack_limit', '1');

    try {
        new Pattern('/(?:\D+|<\d+>)*[!?]/')->replaceUsing(
            static fn (): string => 'x',
            'foobar foobar foobar',
        );

        $this->fail('Expected RegexReplaceFailed to be thrown.');
    } catch (RegexReplaceFailed $regexReplaceFailed) {
        expect($regexReplaceFailed->operation())->toBe('replace')
            ->and($regexReplaceFailed->pregMessage())->toBe('Backtrack limit exhausted')
            ->and($regexReplaceFailed->pregErrorCode())->not->toBe(0);
    } finally {
        ini_set('pcre.backtrack_limit', (string) $previousLimit);
    }
});
