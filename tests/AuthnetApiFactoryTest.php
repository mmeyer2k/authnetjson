<?php

/*
 * This file is part of the AuthnetJSON package.
 *
 * (c) John Conde <stymiee@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JohnConde\Authnet;

class AuthnetApiFactoryTest extends \PHPUnit_Framework_TestCase
{
    private $login;
    private $transactionKey;
    private $server;

    protected function setUp()
    {
        $this->login          = 'test';
        $this->transactionKey = 'test';
    }

    public function testCurlWrapperProductionResponse()
    {
        $server  = AuthnetApiFactory::USE_PRODUCTION_SERVER;
        $authnet = AuthnetApiFactory::getJsonApiHandler($this->login, $this->transactionKey, $server);

        $this->assertInstanceOf('JohnConde\Authnet\CurlWrapper', new CurlWrapper());
    }

    public function testCurlWrapperDevelopmentResponse()
    {
        $server  = AuthnetApiFactory::USE_DEVELOPMENT_SERVER;
        $authnet = AuthnetApiFactory::getJsonApiHandler($this->login, $this->transactionKey, $server);

        $this->assertInstanceOf('JohnConde\Authnet\CurlWrapper', new CurlWrapper());
    }
}