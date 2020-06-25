<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\PhpUnit\Legacy;

use PHPUnit\TextUI\Command as BaseCommand;
use PHPUnit\TextUI\Configuration\Registry;
use PHPUnit\TextUI\TestRunner as BaseRunner;
use PHPUnit\TextUI\XmlConfiguration\Configuration;
use PHPUnit\TextUI\XmlConfiguration\Loader;
use Symfony\Bridge\PhpUnit\SymfonyTestsListener;

/**
 * {@inheritdoc}
 *
 * @internal
 */
class CommandForV9_3 extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function createRunner(): BaseRunner
    {
        $this->arguments['listeners'] = isset($this->arguments['listeners']) ? $this->arguments['listeners'] : [];

        $registeredLocally = false;

        foreach ($this->arguments['listeners'] as $registeredListener) {
            if ($registeredListener instanceof SymfonyTestsListener) {
                $registeredListener->globalListenerDisabled();
                $registeredLocally = true;
                break;
            }
        }

        if (isset($this->arguments['configuration'])) {
            $configuration = $this->arguments['configuration'];
            if (!$configuration instanceof Configuration) {
                $configuration = (new Loader)->load($this->arguments['configuration']);
            }
            foreach ($configuration->listeners() as $registeredListener) {
                if ('Symfony\Bridge\PhpUnit\SymfonyTestsListener' === ltrim($registeredListener->className(), '\\')) {
                    $registeredLocally = true;
                    break;
                }
            }
        }

        if (!$registeredLocally) {
            $this->arguments['listeners'][] = new SymfonyTestsListener();
        }

        return parent::createRunner();
    }
}
