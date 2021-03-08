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

use Polymorphine\Headers\Cookie;
use Polymorphine\Headers\Cookie\Exception\CookieAlreadySentException;
use Polymorphine\Headers\Cookie\Exception\IllegalCharactersException;


class MockedCookie implements Cookie
{
    public $name;
    public $value;
    public $deleted = false;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function send(string $value): void
    {
        if (!is_null($this->value)) {
            throw new CookieAlreadySentException();
        }
        $this->value = $this->valid($value);
    }

    public function revoke(): void
    {
        if (!is_null($this->value)) {
            throw new CookieAlreadySentException();
        }
        $this->deleted = true;
    }

    private function valid(string $value): string
    {
        if (preg_match('#[^a-z0-9A-Z]#', $value)) {
            throw new IllegalCharactersException();
        }
        return $value;
    }
}
