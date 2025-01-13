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


class MockedCookie implements Cookie
{
    public string  $name;
    public ?string $value = null;
    public bool    $deleted = false;

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
        $this->value = $this->valid($value);
    }

    public function revoke(): void
    {
        $this->deleted = true;
    }

    private function valid(string $value): string
    {
        return $value;
    }
}
