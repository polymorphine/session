<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Session package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Session\SessionStorage;

use Polymorphine\Session\SessionStorage;
use Polymorphine\Session\SessionStorageProvider;


class LazySessionStorage implements SessionStorage
{
    private SessionStorageProvider $provider;
    private SessionStorage         $storage;

    /**
     * @param SessionStorageProvider $provider
     */
    public function __construct(SessionStorageProvider $provider)
    {
        $this->provider = $provider;
    }

    public function userId(): ?string
    {
        return $this->storage()->userId();
    }

    public function newUserContext($userId = null): void
    {
        $this->storage()->newUserContext($userId);
    }

    public function has(string $key): bool
    {
        return $this->storage()->has($key);
    }

    public function get(string $key, $default = null)
    {
        return $this->storage()->get($key, $default);
    }

    public function set(string $key, $value): void
    {
        $this->storage()->set($key, $value);
    }

    public function remove(string $key): void
    {
        $this->storage()->remove($key);
    }

    public function clear(): void
    {
        $this->storage()->clear();
    }

    private function storage(): SessionStorage
    {
        return $this->storage ??= $this->provider->storage();
    }
}
