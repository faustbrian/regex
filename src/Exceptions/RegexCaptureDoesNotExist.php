<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Regex\Exceptions;

use RuntimeException;

use function sprintf;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class RegexCaptureDoesNotExist extends RuntimeException implements RegexException
{
    public static function forPatternAndCapture(string $pattern, int|string $capture): self
    {
        return new self(sprintf(
            'Pattern `%s` does not define capture `%s`.',
            $pattern,
            (string) $capture,
        ));
    }
}
