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


interface SessionContext
{
    /**
     * Orders to regenerate session id.
     */
    public function reset(): void;

    /**
     * Saves data from session storage.
     *
     * @param array $data
     */
    public function commit(array $data): void;
}
