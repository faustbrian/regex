<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Regex\MatchResult;
use Cline\Regex\MatchResults;
use Cline\Regex\Pattern;

it('iterates over all matches from a compiled pattern', function (): void {
    $results = new Pattern('/a(b)?/')->matchAll('abac');

    expect($results)->toBeInstanceOf(MatchResults::class)
        ->and($results->matched())->toBeTrue()
        ->and($results->count())->toBe(2);

    $matches = iterator_to_array($results);

    expect($matches[0])->toBeInstanceOf(MatchResult::class)
        ->and($matches[0]->value())->toBe('ab')
        ->and($matches[0]->offset())->toBe(0)
        ->and($matches[0]->capture(1))->toBe('b')
        ->and($matches[0]->captureOffset(1))->toBe(1)
        ->and($matches[1]->value())->toBe('a')
        ->and($matches[1]->offset())->toBe(2)
        ->and($matches[1]->capture(1))->toBeNull()
        ->and($matches[1]->captureOffset(1))->toBeNull();
});

it('returns an empty collection when there are no matches', function (): void {
    $results = new Pattern('/z/')->matchAll('banana');

    expect($results->matched())->toBeFalse()
        ->and($results->count())->toBe(0)
        ->and($results->first())->toBeNull()
        ->and(iterator_to_array($results))->toBe([]);
});

it('supports offsets for global matches', function (): void {
    $results = new Pattern('/a/')->matchAll('banana', offset: 2);

    expect($results->count())->toBe(2)
        ->and($results->first()?->value())->toBe('a')
        ->and($results->first()?->offset())->toBe(3);
});

it('does not evaluate global matches until iterated', function (): void {
    $results = new Pattern('/a/')->matchAll('banana');

    expect($results->resolved())->toBeFalse();

    $results->first();

    expect($results->resolved())->toBeTrue();
});

it('stops lazy global matching after a zero-width match at the end', function (): void {
    $results = new Pattern('/$/')->matchAll('banana');

    expect($results->count())->toBe(1)
        ->and($results->first()?->value())->toBe('')
        ->and($results->first()?->offset())->toBe(6);
});
