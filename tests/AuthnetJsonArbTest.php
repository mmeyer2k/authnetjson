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

class AuthnetJsonArbTest extends \PHPUnit_Framework_TestCase
{
    private $login;
    private $transactionKey;
    private $server;
    private $http;

    protected function setUp()
    {
        $this->login          = 'test';
        $this->transactionKey = 'test';
        $this->server         = AuthnetApiFactory::USE_DEVELOPMENT_SERVER;

        $this->http = $this->getMockBuilder('\JohnConde\Authnet\CurlWrapper')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @covers            \JohnConde\Authnet\AuthnetJson::process()
     * @uses              \JohnConde\Authnet\AuthnetApiFactory::getJsonApiHandler
     * @uses              \JohnConde\Authnet\AuthnetApiFactory::getWebServiceURL
     */
    public function testARBCreateSubscriptionRequestSuccess()
    {
        $request = array(
            'refId' => 'Sample',
            'subscription' => array(
                'name' => 'Sample subscription',
                'paymentSchedule' => array(
                    'interval' => array(
                        'length' => '1',
                        'unit' => 'months'
                    ),
                    'startDate' => '2015-04-18',
                    'totalOccurrences' => '12',
                    'trialOccurrences' => '1'
                ),
                'amount' => '10.29',
                'trialAmount' => '0.00',
                'payment' => array(
                    'creditCard' => array(
                        'cardNumber' => '4111111111111111',
                        'expirationDate' => '2016-08'
                    )
                ),
                'billTo' => array(
                    'firstName' => 'John',
                    'lastName' => 'Smith'
                )
            )
        );
        $responseJson = '{
           "subscriptionId":"2341621",
           "refId":"Sample",
           "messages":{
              "resultCode":"Ok",
              "message":[
                 {
                    "code":"I00001",
                    "text":"Successful."
                 }
              ]
           }
        }';

        $this->http->expects($this->once())
            ->method('process')
            ->will($this->returnValue($responseJson));

        $authnet = AuthnetApiFactory::getJsonApiHandler($this->login, $this->transactionKey, $this->server);
        $authnet->setProcessHandler($this->http);
        $authnet->createTransactionRequest($request);

        $this->assertEquals('Ok', $authnet->messages->resultCode);
        $this->assertEquals('2341621', $authnet->subscriptionId);
        $this->assertEquals('Sample', $authnet->refId);
        $this->assertEquals('I00001', $authnet->messages->message[0]->code);
        $this->assertEquals('Successful.', $authnet->messages->message[0]->text);
    }

    /**
     * @covers            \JohnConde\Authnet\AuthnetJson::process()
     * @uses              \JohnConde\Authnet\AuthnetApiFactory::getJsonApiHandler
     * @uses              \JohnConde\Authnet\AuthnetApiFactory::getWebServiceURL
     */
    public function testARBCreateSubscriptionRequestDuplicateRequestError()
    {
        $request = array(
            'refId' => 'Sample',
            'subscription' => array(
                'name' => 'Sample subscription',
                'paymentSchedule' => array(
                    'interval' => array(
                        'length' => '1',
                        'unit' => 'months'
                    ),
                    'startDate' => '2015-04-18',
                    'totalOccurrences' => '12',
                    'trialOccurrences' => '1'
                ),
                'amount' => '10.29',
                'trialAmount' => '0.00',
                'payment' => array(
                    'creditCard' => array(
                        'cardNumber' => '4111111111111111',
                        'expirationDate' => '2016-08'
                    )
                ),
                'billTo' => array(
                    'firstName' => 'John',
                    'lastName' => 'Smith'
                )
            )
        );
        $responseJson = '{
           "refId":"Sample",
           "messages":{
              "resultCode":"Error",
              "message":[
                 {
                    "code":"E00012",
                    "text":"You have submitted a duplicate of Subscription 2341621. A duplicate subscription will not be created."
                 }
              ]
           }
        }';

        $this->http->expects($this->once())
            ->method('process')
            ->will($this->returnValue($responseJson));

        $authnet = AuthnetApiFactory::getJsonApiHandler($this->login, $this->transactionKey, $this->server);
        $authnet->setProcessHandler($this->http);
        $authnet->createTransactionRequest($request);

        $this->assertEquals('Error', $authnet->messages->resultCode);
        $this->assertEquals('Sample', $authnet->refId);
        $this->assertEquals('E00012', $authnet->messages->message[0]->code);
        $this->assertEquals('You have submitted a duplicate of Subscription 2341621. A duplicate subscription will not be created.', $authnet->messages->message[0]->text);
    }

    /**
     * @covers            \JohnConde\Authnet\AuthnetJson::process()
     * @uses              \JohnConde\Authnet\AuthnetApiFactory::getJsonApiHandler
     * @uses              \JohnConde\Authnet\AuthnetApiFactory::getWebServiceURL
     */
    public function testARBCreateSubscriptionRequestInvalidStartDateError()
    {
        $request = array(
            'refId' => 'Sample',
            'subscription' => array(
                'name' => 'Sample subscription',
                'paymentSchedule' => array(
                    'interval' => array(
                        'length' => '1',
                        'unit' => 'months'
                    ),
                    'startDate' => '2015-04-18',
                    'totalOccurrences' => '12',
                    'trialOccurrences' => '1'
                ),
                'amount' => '10.29',
                'trialAmount' => '0.00',
                'payment' => array(
                    'creditCard' => array(
                        'cardNumber' => '4111111111111111',
                        'expirationDate' => '2016-08'
                    )
                ),
                'billTo' => array(
                    'firstName' => 'John',
                    'lastName' => 'Smith'
                )
            )
        );
        $responseJson = '{
           "refId":"Sample",
           "messages":{
              "resultCode":"Error",
              "message":[
                 {
                    "code":"E00017",
                    "text":"Start Date must not occur before the submission date."
                 }
              ]
           }
        }';

        $this->http->expects($this->once())
            ->method('process')
            ->will($this->returnValue($responseJson));

        $authnet = AuthnetApiFactory::getJsonApiHandler($this->login, $this->transactionKey, $this->server);
        $authnet->setProcessHandler($this->http);
        $authnet->createTransactionRequest($request);

        $this->assertEquals('Error', $authnet->messages->resultCode);
        $this->assertEquals('Sample', $authnet->refId);
        $this->assertEquals('E00017', $authnet->messages->message[0]->code);
        $this->assertEquals('Start Date must not occur before the submission date.', $authnet->messages->message[0]->text);
    }

    /**
     * @covers            \JohnConde\Authnet\AuthnetJson::process()
     * @uses              \JohnConde\Authnet\AuthnetApiFactory::getJsonApiHandler
     * @uses              \JohnConde\Authnet\AuthnetApiFactory::getWebServiceURL
     */
    public function testARBGetSubscriptionStatusRequestActive()
    {
        $request = array(
            'refId' => 'Sample',
            'subscriptionId' => '1207505'
        );
        $responseJson = '{
           "note":"Status with a capital \'S\' is obsolete.",
           "status":"active",
           "Status":"active",
           "statusSpecified":true,
           "StatusSpecified":true,
           "refId":"Sample",
           "messages":{
              "resultCode":"Ok",
              "message":[
                 {
                    "code":"I00001",
                    "text":"Successful."
                 }
              ]
           }
        }';

        $this->http->expects($this->once())
            ->method('process')
            ->will($this->returnValue($responseJson));

        $authnet = AuthnetApiFactory::getJsonApiHandler($this->login, $this->transactionKey, $this->server);
        $authnet->setProcessHandler($this->http);
        $authnet->ARBGetSubscriptionStatusRequest($request);

        $this->assertEquals('Ok', $authnet->messages->resultCode);
        $this->assertEquals('Sample', $authnet->refId);
        $this->assertEquals('I00001', $authnet->messages->message[0]->code);
        $this->assertEquals('Successful.', $authnet->messages->message[0]->text);
        $this->assertEquals('active', $authnet->status);
        $this->assertTrue($authnet->statusSpecified);
    }

    /**
     * @covers            \JohnConde\Authnet\AuthnetJson::process()
     * @uses              \JohnConde\Authnet\AuthnetApiFactory::getJsonApiHandler
     * @uses              \JohnConde\Authnet\AuthnetApiFactory::getWebServiceURL
     */
    public function testARBGetSubscriptionStatusRequestCancelled()
    {
        $request = array(
            'refId' => 'Sample',
            'subscriptionId' => '1207505'
        );
        $responseJson = '{
           "note":"Status with a capital \'S\' is obsolete.",
           "status":"canceled",
           "Status":"canceled",
           "statusSpecified":true,
           "StatusSpecified":true,
           "refId":"Sample",
           "messages":{
              "resultCode":"Ok",
              "message":[
                 {
                    "code":"I00001",
                    "text":"Successful."
                 }
              ]
           }
        }';

        $this->http->expects($this->once())
            ->method('process')
            ->will($this->returnValue($responseJson));

        $authnet = AuthnetApiFactory::getJsonApiHandler($this->login, $this->transactionKey, $this->server);
        $authnet->setProcessHandler($this->http);
        $authnet->ARBGetSubscriptionStatusRequest($request);

        $this->assertEquals('Ok', $authnet->messages->resultCode);
        $this->assertEquals('Sample', $authnet->refId);
        $this->assertEquals('I00001', $authnet->messages->message[0]->code);
        $this->assertEquals('Successful.', $authnet->messages->message[0]->text);
        $this->assertEquals('canceled', $authnet->status);
        $this->assertTrue($authnet->statusSpecified);
    }

    /**
     * @covers            \JohnConde\Authnet\AuthnetJson::process()
     * @uses              \JohnConde\Authnet\AuthnetApiFactory::getJsonApiHandler
     * @uses              \JohnConde\Authnet\AuthnetApiFactory::getWebServiceURL
     */
    public function testARBCancelSubscriptionRequestSuccess()
    {
        $request = array(
            'refId' => 'Sample',
            'subscriptionId' => '1207505'
        );
        $responseJson = '{
           "refId":"Sample",
           "messages":{
              "resultCode":"Ok",
              "message":[
                 {
                    "code":"I00001",
                    "text":"Successful."
                 }
              ]
           }
        }';

        $this->http->expects($this->once())
            ->method('process')
            ->will($this->returnValue($responseJson));

        $authnet = AuthnetApiFactory::getJsonApiHandler($this->login, $this->transactionKey, $this->server);
        $authnet->setProcessHandler($this->http);
        $authnet->ARBGetSubscriptionStatusRequest($request);

        $this->assertEquals('Ok', $authnet->messages->resultCode);
        $this->assertEquals('Sample', $authnet->refId);
        $this->assertEquals('I00001', $authnet->messages->message[0]->code);
        $this->assertEquals('Successful.', $authnet->messages->message[0]->text);
    }

    /**
     * @covers            \JohnConde\Authnet\AuthnetJson::process()
     * @uses              \JohnConde\Authnet\AuthnetApiFactory::getJsonApiHandler
     * @uses              \JohnConde\Authnet\AuthnetApiFactory::getWebServiceURL
     */
    public function testARBCancelSubscriptionRequestAlreadyCancelled()
    {
        $request = array(
            'refId' => 'Sample',
            'subscriptionId' => '1207505'
        );
        $responseJson = '{
           "refId":"Sample",
           "messages":{
              "resultCode":"Ok",
              "message":[
                 {
                    "code":"I00002",
                    "text":"The subscription has already been canceled."
                 }
              ]
           }
        }';

        $this->http->expects($this->once())
            ->method('process')
            ->will($this->returnValue($responseJson));

        $authnet = AuthnetApiFactory::getJsonApiHandler($this->login, $this->transactionKey, $this->server);
        $authnet->setProcessHandler($this->http);
        $authnet->ARBGetSubscriptionStatusRequest($request);

        $this->assertEquals('Ok', $authnet->messages->resultCode);
        $this->assertEquals('Sample', $authnet->refId);
        $this->assertEquals('I00002', $authnet->messages->message[0]->code);
        $this->assertEquals('The subscription has already been canceled.', $authnet->messages->message[0]->text);
    }

    /**
     * @covers            \JohnConde\Authnet\AuthnetJson::process()
     * @uses              \JohnConde\Authnet\AuthnetApiFactory::getJsonApiHandler
     * @uses              \JohnConde\Authnet\AuthnetApiFactory::getWebServiceURL
     */
    public function testARBUpdateSubscriptionRequestSuccess()
    {
        $request = array(
            'refId' => 'Sample',
            'subscriptionId' => '2342682',
            'subscription' => array(
                'payment' => array(
                    'creditCard' => array(
                        'cardNumber' => '6011000000000012',
                        'expirationDate' => '2016-08'
                    ),
                ),
            ),
        );
        $responseJson = '{
           "refId":"Sample",
           "messages":{
              "resultCode":"Ok",
              "message":[
                 {
                    "code":"I00001",
                    "text":"Successful."
                 }
              ]
           }
        }';

        $this->http->expects($this->once())
            ->method('process')
            ->will($this->returnValue($responseJson));

        $authnet = AuthnetApiFactory::getJsonApiHandler($this->login, $this->transactionKey, $this->server);
        $authnet->setProcessHandler($this->http);
        $authnet->ARBGetSubscriptionStatusRequest($request);

        $this->assertEquals('Ok', $authnet->messages->resultCode);
        $this->assertEquals('Sample', $authnet->refId);
        $this->assertEquals('I00001', $authnet->messages->message[0]->code);
        $this->assertEquals('Successful.', $authnet->messages->message[0]->text);
    }

    /**
     * @covers            \JohnConde\Authnet\AuthnetJson::process()
     * @uses              \JohnConde\Authnet\AuthnetApiFactory::getJsonApiHandler
     * @uses              \JohnConde\Authnet\AuthnetApiFactory::getWebServiceURL
     */
    public function testARBUpdateSubscriptionRequestError()
    {
        $request = array(
            'refId' => 'Sample',
            'subscriptionId' => '2342682',
            'subscription' => array(
                'payment' => array(
                    'creditCard' => array(
                        'cardNumber' => '6011000000000012',
                        'expirationDate' => '2016-08'
                    ),
                ),
            ),
        );
        $responseJson = '{
           "refId":"Sample",
           "messages":{
              "resultCode":"Error",
              "message":[
                 {
                    "code":"E00037",
                    "text":"Subscriptions that are canceled cannot be updated."
                 }
              ]
           }
        }';

        $this->http->expects($this->once())
            ->method('process')
            ->will($this->returnValue($responseJson));

        $authnet = AuthnetApiFactory::getJsonApiHandler($this->login, $this->transactionKey, $this->server);
        $authnet->setProcessHandler($this->http);
        $authnet->ARBGetSubscriptionStatusRequest($request);

        $this->assertEquals('Error', $authnet->messages->resultCode);
        $this->assertEquals('Sample', $authnet->refId);
        $this->assertEquals('E00037', $authnet->messages->message[0]->code);
        $this->assertEquals('Subscriptions that are canceled cannot be updated.', $authnet->messages->message[0]->text);
    }
}