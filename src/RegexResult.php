<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Regex;

use function preg_last_error_msg;

/**
 * Shared base for regex result objects that need access to preg error state.
 *
 * @author Brian Faust <brian@cline.sh>
 */
abstract class RegexResult
{
    /**
     * Return the most recent native preg error as a human-readable message.
     */
    protected static function lastPregError(): string
    {
        return preg_last_error_msg();
    }
}
