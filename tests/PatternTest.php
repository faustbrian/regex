<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Regex\Exceptions\RegexCaptureDoesNotExist;
use Cline\Regex\Exceptions\RegexMatchFailed;
use Cline\Regex\MatchResult;
use Cline\Regex\Pattern;

it('matches through a pattern instance', function (): void {
    $match = new Pattern('/(?<name>Brian)/')->match('Brian Faust');

    expect($match)->toBeInstanceOf(MatchResult::class)
        ->and($match->matched())->toBeTrue()
        ->and($match->pattern())->toBe('/(?<name>Brian)/')
        ->and($match->subject())->toBe('Brian Faust')
        ->and($match->value())->toBe('Brian')
        ->and($match->offset())->toBe(0)
        ->and($match->capture('name'))->toBe('Brian')
        ->and($match->captureOffset('name'))->toBe(0)
        ->and($match->capture(1))->toBe('Brian')
        ->and($match->captureOffset(1))->toBe(0)
        ->and($match->captures())->toBe([
            0 => 'Brian',
            'name' => 'Brian',
            1 => 'Brian',
        ]);
});

it('matches through the fluent pattern factory', function (): void {
    $pattern = Pattern::of('/(?<name>Brian)/');
    $match = $pattern->match('Brian Faust');

    expect($pattern->pattern())->toBe('/(?<name>Brian)/')
        ->and($match->matched())->toBeTrue()
        ->and($match->value())->toBe('Brian')
        ->and($match->capture('name'))->toBe('Brian');
});

it('returns null for an unmatched capture that exists in the pattern', function (): void {
    $match = new Pattern('/(?<A>a)|(?<B>b)/')->match('a');

    expect($match->matched())->toBeTrue()
        ->and($match->capture('B'))->toBeNull()
        ->and($match->captureOffset('B'))->toBeNull()
        ->and($match->capture(2))->toBeNull();
});

it('returns an unmatched result when the subject does not match', function (): void {
    $match = new Pattern('/z/')->match('banana');

    expect($match->matched())->toBeFalse()
        ->and($match->value())->toBeNull()
        ->and($match->offset())->toBeNull()
        ->and($match->captures())->toBe([]);
});

it('throws when requesting a capture that does not exist', function (): void {
    $match = new Pattern('/(?<name>Brian)/')->match('Brian Faust');

    expect(fn (): ?string => $match->capture('missing'))
        ->toThrow(RegexCaptureDoesNotExist::class);
});

it('throws when requesting the offset of a capture that does not exist', function (): void {
    $match = new Pattern('/(?<name>Brian)/')->match('Brian Faust');

    expect(fn (): ?int => $match->captureOffset('missing'))
        ->toThrow(RegexCaptureDoesNotExist::class);
});

it('supports offsets for single matches', function (): void {
    $match = new Pattern('/a/')->match('banana', offset: 2);

    expect($match->matched())->toBeTrue()
        ->and($match->value())->toBe('a')
        ->and($match->offset())->toBe(3);
});

it('exposes structured metadata for match failures', function (): void {
    try {
        new Pattern('/abc')->match('abc');

        $this->fail('Expected RegexMatchFailed to be thrown.');
    } catch (RegexMatchFailed $regexMatchFailed) {
        expect($regexMatchFailed->pattern())->toBe('/abc')
            ->and($regexMatchFailed->subject())->toBe('abc')
            ->and($regexMatchFailed->operation())->toBe('match')
            ->and($regexMatchFailed->pregMessage())->toContain('No ending delimiter');
    }
});

it('exposes structured metadata for preg match failures', function (): void {
    $previousLimit = ini_get('pcre.backtrack_limit');
    ini_set('pcre.backtrack_limit', '1');

    try {
        new Pattern('/(?:\D+|<\d+>)*[!?]/')->match('foobar foobar foobar');

        $this->fail('Expected RegexMatchFailed to be thrown.');
    } catch (RegexMatchFailed $regexMatchFailed) {
        expect($regexMatchFailed->operation())->toBe('match')
            ->and($regexMatchFailed->pregMessage())->toBe('Backtrack limit exhausted')
            ->and($regexMatchFailed->pregErrorCode())->not->toBe(0);
    } finally {
        ini_set('pcre.backtrack_limit', (string) $previousLimit);
    }
});
