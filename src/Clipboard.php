<?php

declare(strict_types=1);

namespace Artemeon\Console;

use RuntimeException;

class Clipboard
{
    public static function copy(string $content): bool
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $clip = popen('clip', 'wb');
        } elseif (PHP_OS_FAMILY === 'Linux') {
            $clip = popen('xclip -selection clipboard', 'wb');
        } elseif (PHP_OS_FAMILY === 'Darwin') {
            $clip = popen('pbcopy', 'wb');
        } else {
            throw new RuntimeException('Unsupported OS: ' . PHP_OS_FAMILY . ' - only Windows, Linux and MacOS are supported.');
        }

        if ($clip === false) {
            throw new RuntimeException('Could not open clipboard.');
        }

        $written = fwrite($clip, $content);

        return pclose($clip) === 0 && $written === strlen($content);
    }
}
