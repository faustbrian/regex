<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Regex\Exceptions;

use function sprintf;

/**
 * Exception raised when a requested capture group does not exist.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class RegexGroupDoesNotExist extends RegexFailed
{
    /**
     * Create an exception for a missing captured group.
     */
    public static function forPatternSubjectAndGroup(
        string $pattern,
        string $subject,
        int|string $group,
    ): self {
        return new self(sprintf(
            "Pattern `%s` with subject `%s` didn't capture a group named %s",
            $pattern,
            $subject,
            (string) $group,
        ));
    }
}
