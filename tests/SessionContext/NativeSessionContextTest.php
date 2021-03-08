<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Session package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Session\Tests\SessionContext;

use PHPUnit\Framework\TestCase;
use Polymorphine\Session\SessionContext;
use Polymorphine\Session\SessionStorageProvider;
use Polymorphine\Session\Tests\Fixtures\SessionGlobalState;
use Psr\Http\Server\MiddlewareInterface;
use Polymorphine\Session\Tests\Doubles;
use RuntimeException;

require_once dirname(__DIR__) . '/Fixtures/session-functions.php';


class NativeSessionContextTest extends TestCase
{
    public function tearDown(): void
    {
        SessionGlobalState::reset();
    }

    public function testInstantiation()
    {
        $context = $this->context();
        $this->assertInstanceOf(MiddlewareInterface::class, $context);
        $this->assertInstanceOf(SessionContext::class, $context);
        $this->assertInstanceOf(SessionStorageProvider::class, $context);
    }

    public function testSessionNameIsSynchronizedWithCookieName()
    {
        $cookie = new Doubles\MockedCookie('MySESSION');
        $this->context($cookie)->process($this->request(), $this->handler());
        $this->assertSame('MySESSION', SessionGlobalState::$name);
    }

    public function testSessionInitialization()
    {
        $context = $this->context($cookie);
        $handler = $this->handler(function () use ($context) {
            $context->storage()->set('foo', 'bar');
        });

        $context->process($this->request(), $handler);
        $this->assertSame(['foo' => 'bar'], SessionGlobalState::$data);
        $this->assertSame('DefaultSessionId', $cookie->value);
    }

    public function testSessionResume()
    {
        SessionGlobalState::$data = ['foo' => 'bar'];

        $context = $this->context($cookie);
        $handler = $this->handler(function () use ($context) {
            $session = $context->storage();
            $session->set('foo', $session->get('foo') . '-baz');
        });

        $context->process($this->request(true), $handler);
        $this->assertSame(['foo' => 'bar-baz'], SessionGlobalState::$data);
        $this->assertNull($cookie->value);
    }

    public function testSessionRegenerateId()
    {
        SessionGlobalState::$data = ['foo' => 'bar'];

        $context = $this->context($cookie);
        $handler = $this->handler(function () use ($context) {
            $context->reset();
        });

        $context->process($this->request(true), $handler);
        $this->assertSame(['foo' => 'bar'], SessionGlobalState::$data);
        $this->assertSame('RegeneratedSessionId', $cookie->value);
    }

    public function testSessionDestroy()
    {
        SessionGlobalState::$data = ['foo' => 'bar'];

        $context = $this->context($cookie);
        $handler = $this->handler(function () use ($context) {
            $context->storage()->clear();
        });

        $context->process($this->request(true), $handler);
        $this->assertSame([], SessionGlobalState::$data);
        $this->assertTrue($cookie->deleted);
    }

    public function testClearedSessionWithNewDataRegeneratesId()
    {
        SessionGlobalState::$data = ['foo' => 'bar'];

        $context = $this->context($cookie);
        $handler = $this->handler(function () use ($context) {
            $storage = $context->storage();
            $storage->clear();
            $storage->set('baz', 'qux');
        });

        $context->process($this->request(true), $handler);
        $this->assertSame('RegeneratedSessionId', SessionGlobalState::$id);
        $this->assertSame('RegeneratedSessionId', $cookie->value);
    }

    public function testProcessingWhileSessionStarted_ThrowsException()
    {
        SessionGlobalState::$status = PHP_SESSION_ACTIVE;
        $context = $this->context();

        $this->expectException(RuntimeException::class);
        $context->process($this->request(true), $this->handler());
    }

    public function testCallingSessionWithoutContextProcessing_ThrowsException()
    {
        $context = $this->context();

        $this->expectException(RuntimeException::class);
        $context->storage();
    }

    private function request($cookie = false): Doubles\FakeServerRequest
    {
        $request = new Doubles\FakeServerRequest();

        if ($cookie) {
            $request->cookies[SessionGlobalState::$name] = SessionGlobalState::$id;
        }

        return $request;
    }

    private function handler(callable $process = null): Doubles\FakeRequestHandler
    {
        return new Doubles\FakeRequestHandler($process);
    }

    private function context(&$cookie = null): SessionContext\NativeSessionContext
    {
        $cookie = $cookie ?: new Doubles\MockedCookie(SessionGlobalState::$name);
        return new SessionContext\NativeSessionContext($cookie);
    }
}
