<?php

namespace Amp\Socket\Test\Internal;

use Amp\Socket\Internal;
use PHPUnit\Framework\TestCase;

class functionsTest extends TestCase
{
    public function parseUriDataProvider()
    {
        return [
            [
                'unix:///tmp/test',
                ['unix', 'tmp/test', 0],
            ],
            [
                'udg://test',
                ['udg', 'test', 0],
            ],
            [
                'tcp://test:1234',
                ['tcp', 'test', 1234],
            ],
            [
                'udp://host:4321',
                ['udp', 'host', 4321],
            ],
            [
                'tcp://[2001:db8:85a3:8d3:1319:8a2e:370:7348]:443',
                ['tcp', '[2001:db8:85a3:8d3:1319:8a2e:370:7348]', 443],
            ],
        ];
    }

    /**
     * @dataProvider parseUriDataProvider
     */
    public function testParseUri($uri, $expected)
    {
        $this->assertEquals($expected, Internal\parseUri($uri));
    }

    public function parseUriInvalidUriDataProvider()
    {
        return [
            ['///////'],
        ];
    }

    /**
     * @dataProvider parseUriInvalidUriDataProvider
     */
    public function testParseUriInvalidUri($uri)
    {
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Invalid URI:');

        Internal\parseUri($uri);
    }

    public function parseUriInvalidSchemeDataProvider()
    {
        return [
            ['http://example.com'],
            ['https://example.com'],
            ['xml://example.com'],
        ];
    }

    /**
     * @dataProvider parseUriInvalidSchemeDataProvider
     */
    public function testParseUriInvalidScheme($uri)
    {
        $this->expectException(\Error::class);
        $this->expectExceptionMessageMatches('(Invalid URI scheme (.*); tcp, udp, unix or udg scheme expected)');

        Internal\parseUri($uri);
    }

    public function normalizeBindToOptionDataProvider()
    {
        return [
            [null, null],
            ['127.0.0.1', '127.0.0.1:0'],
            ['127.0.0.1:0', '127.0.0.1:0'],
            ['123.123.123.123:1234', '123.123.123.123:1234'],
            ['[::1]', '[::1]:0'],
            ['[a:b::c]', '[a:b::c]:0'],
            ['[1:2::3]:4', '[1:2::3]:4'],
            ['[0000:abcd:0000:abcd:0000:abcd:0127:2258]:4567', '[0000:abcd:0000:abcd:0000:abcd:0127:2258]:4567'],
        ];
    }

    /**
     * @dataProvider normalizeBindToOptionDataProvider
     */
    public function testNormalizeBindToOption($bindTo, $expected)
    {
        $actual = Internal\normalizeBindToOption($bindTo);
        $this->assertSame($expected, $actual);
    }

    public function normalizeBindToOptionInvalidBindToDataProvider()
    {
        return [
            ['-1.-1.-1.-1'],
            ['a.b.c.d'],
            ['123.123.123.123:-0'],
            ['123.123.123.123:-1234567'],
            ['[0000:abcd:0000:abcd:0000:abcd:0127:2258]:-67899'],
            ['[e:f:g:h]'],
        ];
    }

    /**
     * @dataProvider normalizeBindToOptionInvalidBindToDataProvider
     */
    public function testNormalizeBindToOptionInvalidBindTo($bindTo)
    {
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Invalid bindTo value:');

        Internal\normalizeBindToOption($bindTo);
    }

    public function normalizeBindToOptionInvalidPortDataProvider()
    {
        return [
            ['123.123.123.123:123456'],
            ['[0000:abcd:0000:abcd:0000:abcd:0127:2258]:67899'],
        ];
    }

    /**
     * @dataProvider normalizeBindToOptionInvalidPortDataProvider
     */
    public function testNormalizeBindToOptionInvalidPort($bindTo)
    {
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Invalid port:');

        Internal\normalizeBindToOption($bindTo);
    }

    public function normalizeBindToOptionInvalidIpv6DataProvider()
    {
        return [
            ['[::::]'],
            ['[:::1]'],
        ];
    }

    /**
     * @dataProvider normalizeBindToOptionInvalidIpv6DataProvider
     */
    public function testNormalizeBindToOptionInvalidIpv6($bindTo)
    {
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Invalid IPv6 address:');

        Internal\normalizeBindToOption($bindTo);
    }

    public function normalizeBindToOptionInvalidIpv4DataProvider()
    {
        return [
            ['256.256.256.256'],
            ['1234.12.12.12'],
        ];
    }

    /**
     * @dataProvider normalizeBindToOptionInvalidIpv4DataProvider
     */
    public function testNormalizeBindToOptionInvalidIpv4($bindTo)
    {
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Invalid IPv4 address:');

        Internal\normalizeBindToOption($bindTo);
    }
}
