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

use Polymorphine\Session\SessionStorageProvider;
use Polymorphine\Session\SessionStorage;


class FakeSessionStorageProvider implements SessionStorageProvider
{
    public $storage;

    public function __construct()
    {
        $this->storage = new MockedSessionStorage();
    }

    public function storage(): SessionStorage
    {
        $this->storage->invoked = true;
        return $this->storage;
    }
}
