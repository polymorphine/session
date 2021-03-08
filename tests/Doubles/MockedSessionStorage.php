<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Session package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Session\Tests\Doubles;

use Polymorphine\Session\SessionStorage;


class MockedSessionStorage implements SessionStorage
{
    public bool  $invoked = false;
    public array $called = [];

    public function userId(): ?string
    {
        $this->called(__FUNCTION__, func_get_args());
        return 'userId';
    }

    public function newUserContext($userId = null): void
    {
        $this->called(__FUNCTION__, func_get_args());
    }

    public function has(string $key): bool
    {
        $this->called(__FUNCTION__, func_get_args());
        return true;
    }

    public function get(string $key, $default = null): string
    {
        $this->called(__FUNCTION__, func_get_args());
        return 'something';
    }

    public function set(string $key, $value): void
    {
        $this->called(__FUNCTION__, func_get_args());
    }

    public function remove(string $key): void
    {
        $this->called(__FUNCTION__, func_get_args());
    }

    public function clear(): void
    {
        $this->called(__FUNCTION__, func_get_args());
    }

    private function called(string $method, array $params = []): void
    {
        $this->called = [$method => $params];
    }
}
