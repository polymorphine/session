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

use Polymorphine\Session\SessionContext\SessionData;


interface SessionContext
{
    public function start(): void;

    public function data(): SessionData;

    public function resetContext(): void;

    public function commit(array $data): void;
}
