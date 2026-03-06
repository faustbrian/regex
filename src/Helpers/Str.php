<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Regex\Helpers;

use function str_ends_with;

/**
 * Lightweight string helpers used by the regex package.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Str
{
    /**
     * Determine whether the given string ends with the provided suffix.
     */
    public static function endsWith(string $haystack, string $needle): bool
    {
        if ($needle === '') {
            return true;
        }

        return str_ends_with($haystack, $needle);
    }
}
