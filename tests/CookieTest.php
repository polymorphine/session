<?php

/*
 * This file is part of Polymorphine/Session package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Session\Tests;

use PHPUnit\Framework\TestCase;
use Polymorphine\Session\ResponseHeaders\Cookie;
use LogicException;

require_once __DIR__ . '/Fixtures/time-functions.php';


class CookieTest extends TestCase
{
    public function testInstantiation()
    {
        $this->assertInstanceOf(Cookie::class, $this->cookie('new'));
    }

    /**
     * @dataProvider cookieData
     *
     * @param string $headerLine
     * @param array  $data
     */
    public function testSetupMethods(string $headerLine, array $data)
    {
        $cookie = $this->cookie($data['name']);

        if (isset($data['expires'])) {
            empty($data['expires']) ? $cookie->permanent() : $cookie->expires($data['expires']);
        }
        if (isset($data['domain'])) { $cookie->domain($data['domain']); }
        if (isset($data['path'])) { $cookie->path($data['path']); }
        if (isset($data['secure'])) { $cookie->secure(); }
        if (isset($data['httpOnly'])) { $cookie->httpOnly(); }
        if (isset($data['sameSite']) && $data['sameSite'] === 'Strict') {
            $cookie->sameSiteStrict();
        }
        if (!empty($data['sameSite']) && $data['sameSite'] !== 'Strict') {
            $cookie->sameSiteLax();
        }

        $data['value'] ? $cookie->value($data['value']) : $cookie->remove();
        $this->assertEquals($headerLine, (string) $cookie);
    }

    /**
     * @dataProvider cookieData
     *
     * @param string $headerLine
     * @param array  $data
     */
    public function testArrayConstructors(string $headerLine, array $data)
    {
        $name   = $data['name'];
        $cookie = $this->cookie($name, $data);
        $data['value'] ? $cookie->value($data['value']) : $cookie->remove();
        $this->assertEquals($headerLine, (string) $cookie);
    }

    public function cookieData()
    {
        return [
            ['myCookie=; Path=/; Expires=Thursday, 02-May-2013 00:00:00 UTC; MaxAge=-157680000', [
                'name'  => 'myCookie',
                'value' => null
            ]],
            ['fullCookie=foo; Domain=example.com; Path=/directory/; Expires=Tuesday, 01-May-2018 01:00:00 UTC; MaxAge=3600; Secure; HttpOnly; SameSite=Lax', [
                'name'     => 'fullCookie',
                'value'    => 'foo',
                'secure'   => true,
                'expires'  => 60,
                'httpOnly' => true,
                'domain'   => 'example.com',
                'path'     => '/directory/',
                'sameSite' => true
            ]],
            ['permanentCookie=hash-3284682736487236; Expires=Sunday, 30-Apr-2023 00:00:00 UTC; MaxAge=157680000; HttpOnly; SameSite=Strict', [
                'name'     => 'permanentCookie',
                'value'    => 'hash-3284682736487236',
                'expires'  => false,
                'httpOnly' => true,
                'path'     => '',
                'sameSite' => 'Strict'
            ]]
        ];
    }

    public function testPrefixedNameConstructors()
    {
        $this->assertSame('__Host-name=; Path=/; Secure', (string) Cookie::prefixedHost('name'));
        $this->assertSame('__Secure-name=; Path=/; Secure', (string) Cookie::prefixedSecure('name'));
    }

    /**
     * @dataProvider sameSiteDoubleCalls
     *
     * @param string $firstCall
     * @param string $secondCall
     */
    public function testCookieWithSameSiteDirective_WhenSameSiteCalled_ThrowsException(string $firstCall, string $secondCall)
    {
        $cookie = $this->cookie('CheckLogic');
        $cookie = ($firstCall === 'Lax') ? $cookie->sameSiteLax() : $cookie->sameSiteStrict();
        $this->expectException(LogicException::class);
        ($secondCall === 'Lax') ? $cookie->sameSiteLax() : $cookie->sameSiteStrict();
    }

    public function sameSiteDoubleCalls()
    {
        return [['Lax', 'Strict'], ['Strict', 'Lax'], ['Strict', 'Strict'], ['Lax', 'Lax']];
    }

    public function testSecureAndHostNamePrefixWillSetSecureDirectiveImplicitly()
    {
        $cookie = $this->cookie('__SECURE-name');
        $cookie->path('/test')->domain('example.com')->value('test');
        $headerLine = '__SECURE-name=test; Domain=example.com; Path=/test; Secure';
        $this->assertEquals($headerLine, (string) $cookie);

        $cookie = $this->cookie('__host-name');
        $cookie->value('test');
        $headerLine = '__host-name=test; Path=/; Secure';
        $this->assertEquals($headerLine, (string) $cookie);
    }

    public function testSettingPathForHostNamePrefixedCookie_ThrowsException()
    {
        $cookie = $this->cookie('__Host-name');
        $this->expectException(LogicException::class);
        $cookie->path('/test');
    }

    public function testSettingDomainForHostNamePrefixedCookie_ThrowsException()
    {
        $cookie = $this->cookie('__Host-name');
        $this->expectException(LogicException::class);
        $cookie->domain('example.com');
    }

    private function cookie(string $name, array $attributes = [])
    {
        return $attributes ? Cookie::fromArray($name, $attributes) : new Cookie($name);
    }
}
