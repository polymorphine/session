# Polymorphine/Session
[![Build Status](https://travis-ci.org/shudd3r/polymorphine-session.svg?branch=develop)](https://travis-ci.org/shudd3r/polymorphine-session)
[![Coverage Status](https://coveralls.io/repos/github/shudd3r/polymorphine-session/badge.svg?branch=develop)](https://coveralls.io/github/shudd3r/polymorphine-session?branch=develop)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/polymorphine/session/dev-develop.svg)](https://packagist.org/packages/polymorphine/session)
[![Packagist](https://img.shields.io/packagist/l/polymorphine/session.svg)](https://packagist.org/packages/polymorphine/session)
### HTTP Request session context service handled by PSR-15 middleware

### Installation with [Composer](https://getcomposer.org/)
    php composer.phar require polymorphine/session

### How it works?

In procedural code you would have to call `session_start()` to allow access to superglobal `$_SESSION` array.
This library also requires initialisation phase, but it is achieved with ([PSR-15](https://www.php-fig.org/psr/psr-15/)) middleware.
Request going through this middleware will trigger [`SessionContext`](src/SessionContext.php),
and [`SessionStorage`](src/SessionStorage.php) will become available.

The session storage is not superglobal anymore, and it can be passed explicitly into objects
that require access to its data. The downside is that it **cannot be instantiated directly
_before request processing_** is started, so lazy initialisation is necessary one way or another.

