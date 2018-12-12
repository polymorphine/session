<?php

/*
 * This file is part of Polymorphine/Session package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Session\Tests\SessionStorage;

use PHPUnit\Framework\TestCase;
use Polymorphine\Session\SessionStorage;
use Polymorphine\Session\Tests\Doubles\FakeSessionStorageProvider;


class LazySessionStorageTest extends TestCase
{
    public function testInstantiation()
    {
        $storage = new SessionStorage\LazySessionStorage(new FakeSessionStorageProvider());
        $this->assertInstanceOf(SessionStorage::class, $storage);
    }

    /**
     * @dataProvider methodCalls
     * @param string $method
     * @param array $params
     */
    public function testMethodCalls(string $method, array $params)
    {
        $provider = new FakeSessionStorageProvider();
        $mock     = $provider->storage;
        $storage  = new SessionStorage\LazySessionStorage($provider);
        $this->assertFalse($mock->invoked);

        $storage->$method(...$params);
        $this->assertTrue($mock->invoked);
        $this->assertSame([$method => $params], $mock->called);
    }

    public function methodCalls()
    {
        return [
            ['newUserContext', ['user']],
            ['userId', []],
            ['has', ['key']],
            ['get', ['key', 'default']],
            ['set', ['key', 'value']],
            ['remove', ['key']],
            ['clear', []]
        ];
    }
}
