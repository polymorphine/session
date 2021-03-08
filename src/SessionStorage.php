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

    /**
     * By default an alias to SessionStorage::get('session.user.id').
     *
     * @return mixed User id stored within session data
     */
    public function userId(): ?string;

    /**
     * By default an alias to SessionStorage::set('session.user.id', $value).
     *
     * @param string|null $userId
     */
    public function newUserContext(string $userId = null): void;

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * @param string $key
     * @param null   $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * @param string $key
     * @param $value
     */
    public function set(string $key, $value): void;

    /**
     * @param string $key
     */
    public function remove(string $key): void;

    /**
     * Removes all data stored in session.
     */
    public function clear(): void;
}
