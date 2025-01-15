<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Message\SendMessage;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class MessageControllerTest extends WebTestCase
{
    use InteractsWithMessenger;

    public function test_list_returns_data(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, 'api/messages', [
            'status' => 'sent',
        ]);

        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function test_list_bad_status(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, 'api/messages', [
            'status' => 'failed-to-send',
        ]);

        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function test_that_it_sends_a_message(): void
    {
        $client = static::createClient();
        $client->jsonRequest(Request::METHOD_POST, 'api/messages/send', [
            'text' => 'Hello World',
        ]);

        $this->assertResponseIsSuccessful();
        // This is using https://packagist.org/packages/zenstruck/messenger-test
        $this->transport('sync')
            ->queue()
            ->assertContains(SendMessage::class, 1);
    }

    public function test_that_it_sends_a_bad_message(): void
    {
        $client = static::createClient();
        $client->jsonRequest(Request::METHOD_POST, 'api/messages/send', [
            'text' => '',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
