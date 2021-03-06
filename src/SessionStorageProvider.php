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


interface SessionStorageProvider
{
    /**
     * @return SessionStorage Interface specialized to access & modify session data
     */
    public function storage(): SessionStorage;
}
