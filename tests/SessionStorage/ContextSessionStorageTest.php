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
use Polymorphine\Session\SessionStorage\ContextSessionStorage as Storage;
use Polymorphine\Session\SessionStorage;
use Polymorphine\Session\Tests\Doubles;
use InvalidArgumentException;


class ContextSessionStorageTest extends TestCase
{
    public function test_Instantiation()
    {
        $this->assertInstanceOf(SessionStorage::class, $this->storage());
    }

    public function test_GetData()
    {
        $storage = $this->storage(['foo' => 'bar']);
        $this->assertSame('bar', $storage->get('foo'));
    }

    public function test_SetData()
    {
        $storage = $this->storage();
        $this->assertFalse($storage->has('foo'));
        $storage->set('foo', 'bar');
        $this->assertTrue($storage->has('foo'));
        $this->assertSame('bar', $storage->get('foo'));
    }

    public function test_Set_OverwritesData()
    {
        $storage = $this->storage(['foo' => 'bar']);
        $storage->set('foo', 'baz');
        $this->assertSame('baz', $storage->get('foo'));
    }

    public function test_RemoveData()
    {
        $storage = $this->storage(['foo' => 'bar', 'baz' => true]);
        $storage->remove('foo');
        $this->assertNull($storage->get('foo'));
    }

    public function test_ClearData()
    {
        $storage = $this->storage(['foo' => 'bar', 'baz' => true], $manager);
        $storage->clear();
        $storage->commit();
        $this->assertSame([], $manager->writtenData);
    }

    public function test_DefaultForMissingValues()
    {
        $storage = $this->storage();
        $this->assertSame('default', $storage->get('foo', 'default'));
    }

    public function test_UserId()
    {
        $data    = [SessionStorage::USER_KEY => 'user', 'other' => 'value'];
        $storage = $this->storage($data, $manager);
        $this->assertSame('user', $storage->userId());
        $this->assertNull($storage->get(SessionStorage::USER_KEY));
        $storage->commit();
        $this->assertSame($data, $manager->writtenData);
    }

    public function test_NewUserContext()
    {
        $storage = $this->storage([], $manager);
        $this->assertNull($storage->userId());

        $storage->newUserContext('new');
        $this->assertTrue($manager->resetCalled);
        $this->assertSame('new', $storage->userId());

        $storage->commit();
        $this->assertSame([SessionStorage::USER_KEY => 'new'], $manager->writtenData);
    }

    public function test_Clear_SetsNewUserContext()
    {
        $this->storage([], $manager)->clear();
        $this->assertTrue($manager->resetCalled);
    }

    public function test_SettingDataWithUserKey_ThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->storage()->set(SessionStorage::USER_KEY, 'test');
    }

    public function test_CommitSession()
    {
        $data    = ['foo' => 'bar', 'bar' => 'baz'];
        $storage = $this->storage($data, $manager);

        $storage->set('fizz', 'buzz');
        $storage->commit();
        $this->assertSame($data + ['fizz' => 'buzz'], $manager->writtenData);
    }

    public function test_SettingNull_DoesNotRemoveData()
    {
        $storage = $this->storage(['foo' => 500], $manager);
        $this->assertTrue($storage->has('foo'));
        $storage->set('foo', null);
        $this->assertTrue($storage->has('foo'));
        $storage->commit();
        $this->assertArrayHasKey('foo', $manager->writtenData);
    }

    private function storage(array $data = [], ?Doubles\MockedSessionContext &$manager = null): Storage
    {
        return new Storage($manager ??= new Doubles\MockedSessionContext(), $data);
    }
}
