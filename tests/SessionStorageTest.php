<?php

/*
 * This file is part of Polymorphine/Session package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Session\Tests;

use PHPUnit\Framework\TestCase;
use Polymorphine\Session\SessionStorage;
use Polymorphine\Session\Tests\Doubles\MockedSessionContext;
use InvalidArgumentException;


class SessionStorageTest extends TestCase
{
    public function testInstantiation()
    {
        $this->assertInstanceOf(SessionStorage::class, $session = $this->storage());
    }

    public function testGetData()
    {
        $storage = $this->storage(['foo' => 'bar']);
        $this->assertSame('bar', $storage->get('foo'));
    }

    public function testSetData()
    {
        $storage = $this->storage();
        $this->assertFalse($storage->has('foo'));
        $storage->set('foo', 'bar');
        $this->assertTrue($storage->has('foo'));
        $this->assertSame('bar', $storage->get('foo'));
    }

    public function testSetOverwritesData()
    {
        $storage = $this->storage(['foo' => 'bar']);
        $storage->set('foo', 'baz');
        $this->assertSame('baz', $storage->get('foo'));
    }

    public function testRemoveData()
    {
        $storage = $this->storage(['foo' => 'bar', 'baz' => true]);
        $storage->remove('foo');
        $this->assertNull($storage->get('foo'));
    }

    public function testClearData()
    {
        $storage = $this->storage(['foo' => 'bar', 'baz' => true], $manager);
        $storage->clear();
        $storage->commit();
        $this->assertSame([], $manager->writtenData);
    }

    public function testDefaultForMissingValues()
    {
        $storage = $this->storage();
        $this->assertSame('default', $storage->get('foo', 'default'));
    }

    public function testUserId()
    {
        $data    = [SessionStorage::USER_KEY => 'user', 'other' => 'value'];
        $storage = $this->storage($data, $manager);
        $this->assertSame('user', $storage->userId());
        $this->assertNull($storage->get(SessionStorage::USER_KEY));
        $storage->commit();
        $this->assertSame($data, $manager->writtenData);
    }

    public function testNewUserContext()
    {
        $storage = $this->storage([], $manager);
        $this->assertNull($storage->userId());

        $storage->newUserContext('new');
        $this->assertTrue($manager->resetCalled);
        $this->assertSame('new', $storage->userId());

        $storage->commit();
        $this->assertSame([SessionStorage::USER_KEY => 'new'], $manager->writtenData);
    }

    public function testSettingDataWithUserKey_ThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->storage([])->set(SessionStorage::USER_KEY, 'test');
    }

    public function testCommitSession()
    {
        $data    = ['foo' => 'bar', 'bar' => 'baz'];
        $storage = $this->storage($data, $manager);

        $storage->set('fizz', 'buzz');
        $storage->commit();
        $this->assertSame($data + ['fizz' => 'buzz'], $manager->writtenData);
    }

    public function testSettingNullDoesNotRemoveData()
    {
        $storage = $this->storage(['foo' => 500], $manager);
        $this->assertTrue($storage->has('foo'));
        $storage->set('foo', null);
        $this->assertTrue($storage->has('foo'));
        $storage->commit();
        $this->assertTrue(array_key_exists('foo', $manager->writtenData));
    }

    private function storage(array $data = [], &$manager = null): SessionStorage
    {
        $manager = $manager ?: new MockedSessionContext();
        return new SessionStorage($manager, $data);
    }
}
