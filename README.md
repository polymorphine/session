# Polymorphine/Session
[![Latest stable release](https://poser.pugx.org/polymorphine/session/version)](https://packagist.org/packages/polymorphine/session)
[![Build status](https://github.com/polymorphine/session/workflows/build/badge.svg)](https://github.com/polymorphine/session/actions)
[![Coverage status](https://coveralls.io/repos/github/polymorphine/session/badge.svg?branch=develop)](https://coveralls.io/github/polymorphine/session?branch=develop)
[![PHP version](https://img.shields.io/packagist/php-v/polymorphine/session.svg)](https://packagist.org/packages/polymorphine/session)
[![LICENSE](https://img.shields.io/github/license/polymorphine/session.svg?color=blue)](LICENSE)
### HTTP Request session context service handled by PSR-15 middleware

### Installation with [Composer](https://getcomposer.org/)
```bash
composer require polymorphine/session
```

### How it works?

In procedural code you would have to call `session_start()` to allow access to superglobal `$_SESSION` array.
This library also requires initialisation phase, but it is achieved with ([PSR-15](https://www.php-fig.org/psr/psr-15/)) middleware.
Request going through this middleware will trigger [`SessionContext`](src/SessionContext.php),
and [`SessionStorage`](src/SessionStorage.php) will become available.

The session storage is not superglobal anymore, and it can be passed explicitly into objects
that require access to its data. The downside is that it **cannot be instantiated directly
_before request processing_** is started, so lazy initialisation is necessary one way or another.
