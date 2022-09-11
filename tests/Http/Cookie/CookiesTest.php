<?php

declare(strict_types=1);

namespace Symbiotic\Tests\Http\Cookie {

    use Symbiotic\Http\Cookie\Cookies;
    use PHPUnit\Framework\TestCase as UnitTestCase;
    use Symbiotic\Http\Cookie\CookiesInterface;

    /**
     * @covers Cookies
     */
    class CookiesTest extends UnitTestCase
    {
        /**
         * @covers Cookies::set
         * @return void
         */
        public function testCheckDefaults(): void
        {
            $cookies = new Cookies();
            $defaults = [

                'expires' => 0,
                'httponly' => false,
                'domain' => null,
                'path' => '',
                'secure' => false,
            ];
            $test_cookie = [
                'name' => 'test1',
                'value' => 'value1'
            ];
            $cookies->set($test_cookie['name'], $test_cookie['value']);

            $cookies = $cookies->getResponseCookies();
            $this->assertIsArray($cookies);
            $this->assertTrue(isset($cookies[0]));
            $first_cookie = $cookies[0];
            foreach ($defaults as $k => $v) {
                $this->assertEquals($first_cookie[$k], $v, 'In key:' . $k . ' with value - ' . var_export($v, true));
            }
        }

        /**
         * @covers Cookies::setDefault
         * @return void
         * @throws \Exception
         */
        public function testSetCookieWithDefaults(): void
        {
            $cookies = new Cookies();
            $defaults = [
                'expires' => time() + 1000,
                'path' => '/test',
                'domain' => 'default.com',
                'secure' => true,
                //'httponly' => true,
                'same_site' => CookiesInterface::SAMESITE_STRICT
            ];

            $test_cookie = [
                'name' => 'test1',
                'value' => 'value1'
            ];
            $cookies->setDefaults(
                $defaults['domain'],
                $defaults['path'],
                $defaults['expires'],
                $defaults['secure'],
                $defaults['same_site']
            );

            $cookies->set($test_cookie['name'], $test_cookie['value']);

            $test_cookie = array_merge($test_cookie, $defaults);
            $cookies = $cookies->getResponseCookies();
            $this->assertIsArray($cookies);
            $this->assertTrue(isset($cookies[0]));
            $first_cookie = $cookies[0];
            foreach ($test_cookie as $k => $v) {
                $this->assertEquals($first_cookie[$k], $v, 'In key:' . $k . ' with value - ' . var_export($v, true));
            }
        }

        /**
         * @covers Cookies::setCookie
         * @covers Cookies::getResponseCookies
         * @covers Cookies::remove
         * @return void
         * @throws \Exception
         */
        public function testSetCookieWithDefaultsAndReplace(): void
        {
            $cookies = new Cookies();
            $defaults = [
                'expires' => time() + 1000,
                'path' => '/test',
                'domain' => 'default.com',
                'secure' => true,
                //'httponly' => true,
                'same_site' => CookiesInterface::SAMESITE_STRICT
            ];

            $test_cookie = [
                'name' => 'test1',
                'value' => 'value1',
            ];
            $replace_params = [
                'expires' => time() - 3000,
                'path' => '/docs',
                'domain' => 'test.default.com',
                'secure' => false,

            ];
            $test_cookie = array_merge($test_cookie, $replace_params);
            $options = ['same_site' => CookiesInterface::SAMESITE_NONE];
            $cookies->setDefaults(
                $defaults['domain'],
                $defaults['path'],
                $defaults['expires'],
                $defaults['secure'],
                $defaults['same_site']
            );

            $cookies->setCookie(
                $test_cookie['name'],
                $test_cookie['value'],
                $test_cookie['expires'],
                null,
                $test_cookie['secure'],
                $test_cookie['path'],
                $test_cookie['domain'],
                $options
            );

            //  $test_cookie = array_merge($test_cookie, );
            $cookies = $cookies->getResponseCookies();
            $this->assertIsArray($cookies);
            $this->assertTrue(isset($cookies[0]));
            $first_cookie = $cookies[0];
            foreach ($replace_params as $k => $v) {
                $this->assertEquals($first_cookie[$k], $v, 'In key:' . $k . ' with value - ' . var_export($v, true));
            }
        }

        /**
         * @covers Cookies::setCookie
         * @return void
         */
        public function testSetCookie(): void
        {
            $cookies = new Cookies();
            $test_cookie = [
                'name' => 'test',
                'value' => 'value',
                'expires' => time() + 1000,
                'httponly' => true,
                'path' => '/doc',
                'domain' => 'test.com.cn',
                'secure' => false,
            ];

            $cookies->setCookie(
                $test_cookie['name'],
                $test_cookie['value'],
                $test_cookie['expires'],
                $test_cookie['httponly'],
                $test_cookie['secure'],
                $test_cookie['path'],
                $test_cookie['domain']
            );
            $response_cookies = $cookies->getResponseCookies();
            $this->assertIsArray($response_cookies);
            $this->assertTrue(isset($response_cookies[0]));
            $first_cookie = $response_cookies[0];
            foreach ($test_cookie as $k => $v) {
                $this->assertEquals($first_cookie[$k], $v, 'Key "' . $k . '" value !=' . var_export($v, true));
            }


            /// test delete cookie
            $cookies->remove($test_cookie['name']);
            $response_cookies = [];
            foreach ($cookies->getResponseCookies() as $v) {
                // last!
                $response_cookies[$v['name']] = $v;
            }
            $this->assertTrue(isset($response_cookies[$test_cookie['name']]));
            $this->assertTrue((time() - 3600) > $response_cookies[$test_cookie['name']]['expires']);
        }

        /**
         * @covers Cookies::cookieToResponse
         * @return void
         */
        public function testCookieToHeader(): void
        {
            $cookies = new Cookies();
            $defaults = [
                'expires' => 0,
                'httponly' => false,
                'domain' => null,
                'path' => '',
                'secure' => false
            ];
            $test_cookie = [
                'name' => 'test1',
                'value' => 'value1',
            ];
            $header = $cookies->cookieToResponse($test_cookie);
            $this->assertEquals('test1=value1; ', $header);

            $test_cookie = array_merge($test_cookie, $defaults);

            $header = $cookies->cookieToResponse($test_cookie);
            $this->assertEquals('test1=value1; ', $header);

            $test_cookie['httponly'] = true;
            $header = $cookies->cookieToResponse($test_cookie);
            $this->assertEquals('test1=value1; HttpOnly; ', $header);
            $test_cookie['httponly'] = true;
            $header = $cookies->cookieToResponse($test_cookie);
            $this->assertEquals('test1=value1; HttpOnly; ', $header);
        }

        /**
         * @covers Cookies::get
         * @covers Cookies::setRequestCookies
         * @covers Cookies::has
         * @return void
         */
        public function testRequestCookies(): void
        {
            $request_cookies = [
                'test1' => 'value1',
                'test_array' => [
                    'key1' => 'val_1',
                    'key_2' => 'val2',
                ],
                'test_array_number' => [
                    'val1',
                    'val2',

                ]
            ];

            $cookies = new Cookies();
            $cookies->setRequestCookies($request_cookies);

            foreach ($request_cookies as $k => $v) {
                $this->assertEquals($cookies->get($k), $v);
            }

            $this->assertNull($cookies->get('not_exists'));

            $this->assertEquals('default', $cookies->get('not_exists', 'default'));

            foreach ($request_cookies as $k => $v) {
                $this->assertTrue($cookies->has($k));
            }

            $this->assertFalse($cookies->has('not_exists'));
        }

        /**
         * @covers Cookies::offsetGet
         * @covers Cookies::offsetExists
         * @covers Cookies::offsetSet
         * @covers Cookies::offsetUnset
         * @return void
         */
        public function testArrayAccess(): void
        {
            $cookies = new Cookies();

            $request_cookies = [
                'test1' => 'value1',
                'test_array' => [
                    'key1' => 'val_1',
                    'key_2' => 'val2',
                ],
                'test_array_number' => [
                    'val1',
                    'val2',

                ]
            ];

            $this->assertInstanceOf(\ArrayAccess::class, $cookies);
            $cookies->setRequestCookies($request_cookies);

            foreach ($request_cookies as $k => $v) {
                $this->assertTrue(isset($cookies[$k]));
                $this->assertEquals($cookies[$k], $v);
            }

            $test_cookies = [
                [
                    'name' => 'new_cookie1',
                    'value' => 'value1'
                ],
                [
                    'name' => 'new_cookie2',
                    'value' => 'value2'
                ],
            ];

            foreach ($test_cookies as $v) {
                $cookies[$v['name']] = $v['value'];
            }


            $response_cookies = [];

            foreach ($cookies->getResponseCookies() as $v) {
                $response_cookies[$v['name']] = $v;
            }

            foreach ($test_cookies as  $v) {
                $this->assertTrue(isset($response_cookies[$v['name']]));
                $this->assertEquals($v['value'], $response_cookies[$v['name']]['value']);
            }
            /// test delete cookie
            foreach ($test_cookies as $v) {
                unset($cookies[$v['name']]);
                $this->assertFalse($cookies->has($v['name']));
            }

            $response_cookies = [];

            foreach ($cookies->getResponseCookies() as $v) {
                $response_cookies[$v['name']] = $v;
            }

            foreach ($test_cookies as $v) {
                $this->assertTrue(isset($response_cookies[$v['name']]));
                $this->assertTrue((time() - 3600) > $response_cookies[$v['name']]['expires']);
            }
        }
    }
}