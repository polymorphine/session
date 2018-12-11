<?php

/*
 * This file is part of Polymorphine/Session package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Session\SessionContext;

use Psr\Http\Server\MiddlewareInterface;
use Polymorphine\Session\SessionContext;
use Polymorphine\Session\SessionStorageProvider;
use Polymorphine\Session\SessionStorage;
use Polymorphine\Headers\Cookie;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;


class NativeSessionContext implements MiddlewareInterface, SessionContext, SessionStorageProvider
{
    /** @var SessionStorage\ContextSessionStorage */
    private $sessionData;
    private $cookie;

    private $sessionStarted = false;

    public function __construct(Cookie $cookie)
    {
        $this->cookie = $cookie;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->containsSessionCookie($request)) { $this->start(); }
        $this->sessionData = new SessionStorage\ContextSessionStorage($this, $_SESSION ?? []);

        $response = $handler->handle($request);
        $this->sessionData->commit();

        return $response;
    }

    public function storage(): SessionStorage
    {
        if (!$this->sessionData) {
            throw new RuntimeException('Session context not started');
        }
        return $this->sessionData;
    }

    public function reset(): void
    {
        if (!$this->sessionStarted) { return; }
        session_regenerate_id(true);
        $this->cookie->send(session_id());
    }

    public function commit(array $data): void
    {
        if (!$data) {
            $this->destroy();
            return;
        }

        if (!$this->sessionStarted) {
            $this->start();
            $this->cookie->send(session_id());
        }

        if ($_SESSION === $data) { return; }

        $_SESSION = $data;
        session_write_close();
    }

    private function start(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            throw new RuntimeException('Session started in another context');
        }

        session_start();
        $this->sessionStarted = true;
    }

    private function destroy(): void
    {
        if (!$this->sessionStarted) { return; }

        $this->cookie->revoke();
        session_destroy();
    }

    private function containsSessionCookie(ServerRequestInterface $request): bool
    {
        $sessionName = $this->cookie->name();
        if (session_name() !== $sessionName) { session_name($sessionName); }

        $cookies = $request->getCookieParams();
        return isset($cookies[$sessionName]);
    }
}
