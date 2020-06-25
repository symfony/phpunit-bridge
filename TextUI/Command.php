<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\PhpUnit\TextUI;

if (version_compare(\PHPUnit\Runner\Version::id(), '6.0.0', '<')) {
    class_alias('Symfony\Bridge\PhpUnit\Legacy\CommandForV5', 'Symfony\Bridge\PhpUnit\TextUI\Command');
} elseif (version_compare(\PHPUnit\Runner\Version::id(), '9.0.0', '<')) {
    class_alias('Symfony\Bridge\PhpUnit\Legacy\CommandForV6', 'Symfony\Bridge\PhpUnit\TextUI\Command');
} elseif (version_compare(\PHPUnit\Runner\Version::id(), '9.2.0', '<')) {
    class_alias('Symfony\Bridge\PhpUnit\Legacy\CommandForV9', 'Symfony\Bridge\PhpUnit\TextUI\Command');
} else {
    class_alias('Symfony\Bridge\PhpUnit\Legacy\CommandForV9_3', 'Symfony\Bridge\PhpUnit\TextUI\Command');
}


if (false) {
    class Command
    {
    }
}
