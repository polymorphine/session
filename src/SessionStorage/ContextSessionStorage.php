<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Session package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Session\SessionStorage;

use Polymorphine\Session\SessionStorage;
use Polymorphine\Session\SessionContext;
use InvalidArgumentException;


class ContextSessionStorage implements SessionStorage
{
    private SessionContext $context;
    private array          $data;
    private ?string        $userId;

    /**
     * @param SessionContext $context
     * @param array          $data
     */
    public function __construct(SessionContext $context, array $data = [])
    {
        $this->context = $context;
        $this->userId  = $this->pullUserId($data);
        $this->data    = $data;
    }

    public function newUserContext(string $userId = null): void
    {
        $this->userId = $userId;
        $this->context->reset();
    }

    public function userId(): ?string
    {
        return $this->userId;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function get(string $key, $default = null)
    {
        return $this->has($key) ? $this->data[$key] : $default;
    }

    public function set(string $key, $value): void
    {
        if ($key === self::USER_KEY) {
            $message = 'Key `%s` is reserved for user id and cannot be set directly';
            throw new InvalidArgumentException(sprintf($message, self::USER_KEY));
        }
        $this->data[$key] = $value;
    }

    public function remove(string $key): void
    {
        unset($this->data[$key]);
    }

    public function clear(): void
    {
        $this->data = [];
        $this->newUserContext();
    }

    /**
     * Orders to pass stored data to session context.
     */
    public function commit(): void
    {
        $userId = $this->userId ? [self::USER_KEY => $this->userId] : [];
        $this->context->commit($userId + $this->data);
    }

    private function pullUserId(array &$data): ?string
    {
        $userId = $data[self::USER_KEY] ?? null;
        if ($userId) { unset($data[self::USER_KEY]); }
        return $userId;
    }
}
