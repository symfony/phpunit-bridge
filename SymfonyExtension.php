<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\PhpUnit;

use PHPUnit\Event\Code\Test;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\BeforeTestMethodErrored;
use PHPUnit\Event\Test\BeforeTestMethodErroredSubscriber;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\ErroredSubscriber;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\Test\SkippedSubscriber;
use PHPUnit\Metadata\Group;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use Symfony\Bridge\PhpUnit\Extension\EnableClockMockSubscriber;
use Symfony\Bridge\PhpUnit\Extension\RegisterClockMockSubscriber;
use Symfony\Bridge\PhpUnit\Extension\RegisterDnsMockSubscriber;
use Symfony\Component\ErrorHandler\DebugClassLoader;

class SymfonyExtension implements Extension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        if (class_exists(DebugClassLoader::class)) {
            DebugClassLoader::enable();
        }

        if ($parameters->has('clock-mock-namespaces')) {
            foreach (explode(',', $parameters->get('clock-mock-namespaces')) as $namespace) {
                ClockMock::register($namespace.'\DummyClass');
            }
        }

        $facade->registerSubscriber(new RegisterClockMockSubscriber());
        $facade->registerSubscriber(new EnableClockMockSubscriber());
        $facade->registerSubscriber(new class implements ErroredSubscriber {
            public function notify(Errored $event): void
            {
                SymfonyExtension::disableClockMock($event->test());
                SymfonyExtension::disableDnsMock($event->test());
            }
        });
        $facade->registerSubscriber(new class implements FinishedSubscriber {
            public function notify(Finished $event): void
            {
                SymfonyExtension::disableClockMock($event->test());
                SymfonyExtension::disableDnsMock($event->test());
            }
        });
        $facade->registerSubscriber(new class implements SkippedSubscriber {
            public function notify(Skipped $event): void
            {
                SymfonyExtension::disableClockMock($event->test());
                SymfonyExtension::disableDnsMock($event->test());
            }
        });

        if (interface_exists(BeforeTestMethodErroredSubscriber::class)) {
            $facade->registerSubscriber(new class implements BeforeTestMethodErroredSubscriber {
                public function notify(BeforeTestMethodErrored $event): void
                {
                    if (method_exists($event, 'test')) {
                        SymfonyExtension::disableClockMock($event->test());
                        SymfonyExtension::disableDnsMock($event->test());
                    } else {
                        ClockMock::withClockMock(false);
                        DnsMock::withMockedHosts([]);
                    }
                }
            });
        }

        if ($parameters->has('dns-mock-namespaces')) {
            foreach (explode(',', $parameters->get('dns-mock-namespaces')) as $namespace) {
                DnsMock::register($namespace.'\DummyClass');
            }
        }

        $facade->registerSubscriber(new RegisterDnsMockSubscriber());
    }

    /**
     * @internal
     */
    public static function disableClockMock(Test $test): void
    {
        if (self::hasGroup($test, 'time-sensitive')) {
            ClockMock::withClockMock(false);
        }
    }

    /**
     * @internal
     */
    public static function disableDnsMock(Test $test): void
    {
        if (self::hasGroup($test, 'dns-sensitive')) {
            DnsMock::withMockedHosts([]);
        }
    }

    /**
     * @internal
     */
    public static function hasGroup(Test $test, string $groupName): bool
    {
        if (!$test instanceof TestMethod) {
            return false;
        }

        foreach ($test->metadata() as $metadata) {
            if ($metadata instanceof Group && $groupName === $metadata->groupName()) {
                return true;
            }
        }

        return false;
    }
}
