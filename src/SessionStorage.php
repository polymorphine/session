<?php

/*
 * This file is part of Polymorphine/Session package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Session;


interface SessionStorage
{
    public const USER_KEY = 'session.user.id';

    public function userId();

    public function newUserContext($userId = null): void;

    public function has(string $key): bool;

    public function get(string $key, $default = null);

    public function set(string $key, $value): void;

    public function remove(string $key): void;

    public function clear(): void;
}
