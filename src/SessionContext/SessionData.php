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

use Polymorphine\Session\SessionContext;


class SessionData
{
    private $context;
    private $data;

    public function __construct(SessionContext $context, array $data = [])
    {
        $this->context = $context;
        $this->data    = $data;
    }

    public function get(string $key, $default = null)
    {
        return $this->has($key) ? $this->data[$key] : $default;
    }

    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function remove(string $key): void
    {
        unset($this->data[$key]);
    }

    public function clear(): void
    {
        $this->data = [];
    }

    public function resetContext()
    {
        $this->context->reset();
    }

    public function commit(): void
    {
        $this->context->commit($this->data);
    }
}
