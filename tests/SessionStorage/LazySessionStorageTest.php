<?php declare(strict_types=1);

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
use Polymorphine\Session\Tests\Doubles;


class LazySessionStorageTest extends TestCase
{
    public static function methodCalls(): iterable
    {
        return [
            [fn (SessionStorage $storage) => $storage->newUserContext('user'), ['newUserContext' => ['user']]],
            [fn (SessionStorage $storage) => $storage->userId(), ['userId' => []]],
            [fn (SessionStorage $storage) => $storage->has('key'), ['has' => ['key']]],
            [fn (SessionStorage $storage) => $storage->get('key'), ['get' => ['key', null]]],
            [fn (SessionStorage $storage) => $storage->set('key', 'value'), ['set' => ['key', 'value']]],
            [fn (SessionStorage $storage) => $storage->remove('key'), ['remove' => ['key']]],
            [fn (SessionStorage $storage) => $storage->clear(), ['clear' => []]]
        ];
    }

    public function test_Instantiation()
    {
        $storage = new SessionStorage\LazySessionStorage(new Doubles\FakeSessionStorageProvider());
        $this->assertInstanceOf(SessionStorage::class, $storage);
    }

    /** @dataProvider methodCalls */
    public function test_MethodCalls(callable $storageCall, array $expectedMockLog)
    {
        $provider = new Doubles\FakeSessionStorageProvider();
        $storage  = new SessionStorage\LazySessionStorage($provider);
        $this->assertFalse($provider->storage->invoked);

        $storageCall($storage);
        $this->assertTrue($provider->storage->invoked);
        $this->assertSame($expectedMockLog, $provider->storage->called);
    }
}
