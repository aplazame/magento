<?php

abstract class Aplazame_Sdk_Http_AbstractClientTestCase extends PHPUnit_Framework_TestCase
{
    public function testImplementsClientInterface()
    {
        $client = $this->createClient();

        self::assertInstanceOf('Aplazame_Sdk_Http_ClientInterface', $client);
    }

    /**
     * @dataProvider requestProvider
     */
    public function testSend(Aplazame_Sdk_Http_RequestInterface $request)
    {
        $client = $this->createClient();

        $response = $client->send($request);

        self::assertInstanceOf('Aplazame_Sdk_Http_ResponseInterface', $response);

        $responseBody = json_decode($response->getBody(), true);

        self::assertEquals($request->getUri(), $responseBody['url']);
        self::assertArrayHasKey('X-Foo', $responseBody['headers']);
        self::assertEquals('fooValue', $responseBody['headers']['X-Foo']);
        $body = $request->getBody();
        if ($body) {
            self::assertEquals($body, $responseBody['data']);
        }
    }

    /**
     * @dataProvider requestWithoutResponseBodyProvider
     */
    public function testRequestWithoutResponseBody(Aplazame_Sdk_Http_RequestInterface $request, $statusCode)
    {
        $client = $this->createClient();

        $response = $client->send($request);

        self::assertInstanceOf('Aplazame_Sdk_Http_ResponseInterface', $response);

        self::assertEquals($statusCode, $response->getStatusCode());
    }

    /**
     * @dataProvider invalidRequestProvider
     */
    public function testSendThrowException(Aplazame_Sdk_Http_RequestInterface $request, $exceptionClass)
    {
        $client = $this->createClient();

        $this->setExpectedException($exceptionClass);

        $client->send($request);
    }

    public function requestProvider()
    {
        $headers = array('X-Foo' => array('fooValue'));
        $testBody = 'testBody';

        return array(
            // Description => [request]
            'delete' => array(new Aplazame_Sdk_Http_Request('delete', 'http://httpbin.org/delete', $headers)),
            'get' => array(new Aplazame_Sdk_Http_Request('get', 'http://httpbin.org/get', $headers)),
            'patch' => array(new Aplazame_Sdk_Http_Request('patch', 'http://httpbin.org/patch', $headers, $testBody)),
            'post' => array(new Aplazame_Sdk_Http_Request('post', 'http://httpbin.org/post', $headers, $testBody)),
            'put' => array(new Aplazame_Sdk_Http_Request('put', 'http://httpbin.org/put', $headers, $testBody)),
        );
    }

    public function requestWithoutResponseBodyProvider()
    {
        return array(
            // Description => [request, status code]
            '500' => array(new Aplazame_Sdk_Http_Request('get', 'http://httpbin.org/status/500'), 500),
        );
    }

    public function invalidRequestProvider()
    {
        $runtimeException = 'RuntimeException';

        return array(
            // Description => [request, exceptionClass]
            'Bad host' => array(new Aplazame_Sdk_Http_Request('get', 'http://notexists'), $runtimeException),
        );
    }

    /**
     * @return Aplazame_Sdk_Http_ClientInterface
     */
    abstract protected function createClient();
}
