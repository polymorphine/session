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
use Polymorphine\Session\SessionContext;
use Polymorphine\Session\Tests\Doubles\FakeRequestHandler;
use Polymorphine\Session\Tests\Doubles\DummyResponse;
use Polymorphine\Session\Tests\Doubles\FakeServerRequest;
use Polymorphine\Session\Tests\Doubles\MockedCookie;
use Polymorphine\Session\Tests\Fixtures\SessionGlobalState;
use Psr\Http\Server\MiddlewareInterface;
use RuntimeException;

require_once __DIR__ . '/Fixtures/session-functions.php';


class NativeSessionContextTest extends TestCase
{
    public function tearDown()
    {
        SessionGlobalState::reset();
    }

    public function testInstantiation()
    {
        $context = $this->context();
        $this->assertInstanceOf(MiddlewareInterface::class, $context);
        $this->assertInstanceOf(SessionContext::class, $context);
    }

    public function testCookieNameIsSetToSessionName()
    {
        $this->context($cookie);
        $this->assertSame(SessionGlobalState::$name, $cookie->name);
    }

    public function testSessionInitialization()
    {
        $context = $this->context($cookie);
        $handler = $this->handler(function () use ($context) {
            $context->data()->set('foo', 'bar');
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
            $session = $context->data();
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
            $context->resetContext();
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
            $context->data()->clear();
        });

        $context->process($this->request(true), $handler);
        $this->assertSame([], SessionGlobalState::$data);
        $this->assertTrue($cookie->deleted);
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
        $context->data();
    }

    private function request($cookie = false)
    {
        $request = new FakeServerRequest();

        if ($cookie) {
            $request->cookies[SessionGlobalState::$name] = SessionGlobalState::$id;
        }

        return $request;
    }

    private function handler(callable $process = null)
    {
        return new FakeRequestHandler($process);
    }

    private function context(&$cookie = null)
    {
        $cookie = new MockedCookie();
        return new SessionContext\NativeSessionContext($cookie);
    }
}
