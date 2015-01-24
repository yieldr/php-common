<?php

namespace Yieldr\Http\Negotiation;

use Silex\Application;
use Silex\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class NegotiationServiceProviderTest extends WebTestCase
{
    public function createApplication()
    {
        $app = new NegotiationApplication();

        $app['debug'] = true;

        $app->register(new NegotiationServiceProvider(), [
            'negotiator.format.priorities'   => ['application/json', 'application/xml'],
            'negotiator.language.priorities' => ['en-US', 'en', 'el'],
        ]);

        $app['serializer'] = new SerializerMock();

        $app->get('/test', function (Application $app) {
            return $app->respond(['hello' => 'world']);
        });

        return $app;
    }

    public function testNegotiation()
    {
        $client = $this->createClient();

        $client->request('GET', '/test');
        $response = $client->getResponse();
        $this->assertEquals($response->headers->get('Content-Type'), 'application/xml');

        $client->request('GET', '/test', [], [], ['HTTP_ACCEPT' => 'application/json']);
        $response = $client->getResponse();
        $this->assertEquals($response->headers->get('Content-Type'), 'application/json');
    }
}