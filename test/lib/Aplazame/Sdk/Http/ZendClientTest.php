<?php

/**
 * @covers Aplazame_Aplazame_Http_ZendClient
 */
class Aplazame_Aplazame_Http_ZendClientTest extends Aplazame_Sdk_Http_AbstractClientTestCase
{
    protected function createClient()
    {
        return new Aplazame_Aplazame_Http_ZendClient;
    }
}
