<?php

/*
 * This file is part of Polymorphine/Session package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Session\ResponseHeaders;

use LogicException;
use DateTime;


class Cookie
{
    private const MAX_TIME = 2628000;

    private $name;
    private $value;

    private $minutes;
    private $domain;
    private $path     = '/';
    private $hostLock = false;
    private $secure   = false;
    private $httpOnly = false;
    private $sameSite;

    /**
     * Creates default cookie directive with given name.
     * Prefixed name will force following settings:
     * __Secure- force: secure
     * __Host-   force & lock: secure, domain (current) & path (root).
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $this->parsedPrefix($name);
    }

    /**
     * Cookie attributes can be set with following $attributes array keys:
     * - domain   => (string) override default domain (current request domain)
     * - path     => (string) override default cookie path (root path)
     * - expires  => (int) minutes (empty value will set permanent cookie)
     * - httpOnly => (bool) true will override default (false)
     * - secure   => (bool) true will override default (false)
     * - sameSite => (string) 'Strict'|'Lax' ('Lax' will be also set for any non-empty value).
     *
     * @param $name
     * @param array $attributes
     *
     * @return Cookie
     */
    public static function fromArray($name, $attributes = []): self
    {
        $cookie = new self($name);
        if (isset($attributes['domain'])) {
            $cookie->domain($attributes['domain']);
        }
        if (isset($attributes['path'])) { $cookie->path($attributes['path']); }
        if (array_key_exists('expires', $attributes)) {
            $attributes['expires'] ? $cookie->expires($attributes['expires']) : $cookie->permanent();
        }
        if (!empty($attributes['httpOnly'])) { $cookie->httpOnly(); }
        if (!empty($attributes['secure'])) { $cookie->secure(); }
        if (!empty($attributes['sameSite'])) {
            $cookie->setSameSiteDirective($attributes['sameSite'] === 'Strict' ? 'Strict' : 'Lax');
        }
        return $cookie;
    }

    public static function prefixedSecure(string $name, array $attributes = []): self
    {
        $name = '__Secure-' . $name;
        return $attributes ? self::fromArray($name, $attributes) : new self($name);
    }

    public static function prefixedHost(string $name, array $attributes = []): self
    {
        $name = '__Host-' . $name;
        return $attributes ? self::fromArray($name, $attributes) : new self($name);
    }

    public function __toString()
    {
        return $this->header();
    }

    /**
     * Sets cookie value.
     *
     * @param string $value
     *
     * @return static
     */
    public function value(string $value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Remove cookie header directive.
     *
     * @return static
     */
    public function remove(): self
    {
        $this->minutes = -self::MAX_TIME;
        $this->value   = null;
        return $this;
    }

    /**
     * Sets expiry datetime relative to current time in minutes.
     *
     * @param int $minutes
     *
     * @return static
     */
    public function expires(int $minutes): self
    {
        $this->minutes = $minutes;
        return $this;
    }

    /**
     * Sets expiry date that makes cookie practically permanent.
     *
     * @return static
     */
    public function permanent(): self
    {
        $this->minutes = self::MAX_TIME;
        return $this;
    }

    /**
     * Sets cookie domain.
     *
     * If cookie name has '__Host-' prefix this value cannot be changed and
     * LogicException will be thrown.
     *
     * @param string $domain
     *
     * @throws LogicException
     *
     * @return static
     */
    public function domain(string $domain): self
    {
        if ($this->hostLock) {
            throw new LogicException('Cannot set domain in cookies with `__Host-` name prefix');
        }

        $this->domain = $domain;
        return $this;
    }

    /**
     * Sets cookie path.
     *
     * If cookie name has '__Host-' prefix this value cannot be changed and
     * LogicException will be thrown.
     *
     * @param string $path
     *
     * @throws LogicException
     *
     * @return static
     */
    public function path(string $path): self
    {
        if ($this->hostLock) {
            throw new LogicException('Cannot set path in cookies with `__Host-` name prefix');
        }

        $this->path = $path;
        return $this;
    }

    /**
     * Sets HttpOnly cookie directive that orders browsers to deny access
     * to its content from client-side executable scripts.
     *
     * @return static
     */
    public function httpOnly(): self
    {
        $this->httpOnly = true;
        return $this;
    }

    /**
     * Sets Secure cookie directive that prevents cookie to be sent with
     * unencrypted protocol (http), so its content cannot be intercepted.
     *
     * @return static
     */
    public function secure(): self
    {
        $this->secure = true;
        return $this;
    }

    /**
     * Sets 'Strict' as SameSite attribute which orders browsers to sent
     * cookie back only when website is called directly.
     *
     * @return static
     */
    public function sameSiteStrict(): self
    {
        $this->setSameSiteDirective('Strict');
        return $this;
    }

    /**
     * Sets 'Lax' as SameSite attribute which orders browsers to sent
     * cookie back only when website is called directly or with GET method.
     *
     * @return static
     */
    public function sameSiteLax(): self
    {
        $this->setSameSiteDirective('Lax');
        return $this;
    }

    private function setSameSiteDirective(string $value): void
    {
        if ($this->sameSite) {
            throw new LogicException('SameSite cookie directive already set and cannot be changed');
        }
        $this->sameSite = $value;
    }

    private function parsedPrefix(string $name): string
    {
        if ($name[0] !== '_') { return $name; }

        $secure = (stripos($name, '__Secure-') === 0);
        $host   = !$secure && (stripos($name, '__Host-') === 0);

        $this->secure   = $host || $secure;
        $this->hostLock = $host;

        return $name;
    }

    private function header(): string
    {
        $header = $this->name . '=' . $this->value;

        if ($this->domain) {
            $header .= '; Domain=' . (string) $this->domain;
        }

        if ($this->path) {
            $header .= '; Path=' . $this->path;
        }

        if ($this->minutes) {
            $seconds = $this->minutes * 60;
            $expire  = (new DateTime())->setTimestamp(time() + $seconds)->format(DateTime::COOKIE);

            $header .= '; Expires=' . $expire;
            $header .= '; MaxAge=' . $seconds;
        }

        if ($this->secure) {
            $header .= '; Secure';
        }

        if ($this->httpOnly) {
            $header .= '; HttpOnly';
        }

        if ($this->sameSite) {
            $header .= '; SameSite=' . $this->sameSite;
        }

        return $header;
    }
}
