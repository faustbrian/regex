<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Regex\Exceptions\RegexException;
use Cline\Regex\MatchResult;
use Cline\Regex\MatchResults;
use Cline\Regex\Pattern;
use Cline\Regex\ReplaceResult;

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
arch('source files use strict types')
    ->expect('Cline\\Regex')
    ->toUseStrictTypes();

arch('core value objects are final')
    ->expect([
        Pattern::class,
        MatchResult::class,
        MatchResults::class,
        ReplaceResult::class,
    ])
    ->toBeFinal();

arch('public immutable value objects are readonly')
    ->expect([
        Pattern::class,
        MatchResult::class,
        ReplaceResult::class,
    ])
    ->toBeReadonly();

arch('match result collections stay final')
    ->expect(MatchResults::class)
    ->toBeFinal();

arch('exceptions stay in the exceptions namespace')
    ->expect('Cline\\Regex\\Exceptions')
    ->classes()
    ->toImplement(RegexException::class);
