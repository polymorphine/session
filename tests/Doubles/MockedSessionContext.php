<?php

/*
 * This file is part of Polymorphine/Session package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Session\Tests\Doubles;

use Polymorphine\Session\SessionContext\Session;
use Polymorphine\Session\SessionContext;


class MockedSessionContext implements SessionContext
{
    public $writtenData;
    public $resetCalled = false;

    public function start(): void
    {
    }

    public function data(): Session
    {
    }

    public function reset(): void
    {
        $this->resetCalled = true;
    }

    public function commit(array $data): void
    {
        $this->writtenData = $data;
    }
}
