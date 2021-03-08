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

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;


class FakeRequestHandler implements RequestHandlerInterface
{
    private $process;

    public function __construct(callable $process = null)
    {
        $this->process = $process;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->process) { ($this->process)(); }
        return new DummyResponse();
    }
}
