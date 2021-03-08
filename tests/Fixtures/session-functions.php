<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Session package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Session\SessionContext;

use Polymorphine\Session\Tests\Fixtures\SessionGlobalState;

function session_start()
{
    global $_SESSION;

    $_SESSION = SessionGlobalState::$data;

    SessionGlobalState::$status = PHP_SESSION_ACTIVE;
    SessionGlobalState::$id     = 'DefaultSessionId';
}

function session_status()
{
    return SessionGlobalState::$status;
}

function session_name(string $name = null)
{
    return $name ? SessionGlobalState::$name = $name : SessionGlobalState::$name;
}

function session_id(string $id = null)
{
    return $id ? SessionGlobalState::$id = $id : SessionGlobalState::$id;
}

function session_write_close()
{
    global $_SESSION;

    SessionGlobalState::$status = PHP_SESSION_NONE;
    SessionGlobalState::$data   = $_SESSION;

    $_SESSION = null;
}

function session_destroy()
{
    global $_SESSION;

    $_SESSION = null;

    SessionGlobalState::$data   = [];
    SessionGlobalState::$status = PHP_SESSION_NONE;
    SessionGlobalState::$id     = '';
}

function session_regenerate_id()
{
    SessionGlobalState::$id = 'RegeneratedSessionId';
}
