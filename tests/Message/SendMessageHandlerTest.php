<?php

declare(strict_types=1);

namespace App\Tests\Message;

use App\Config\MessageStatus;
use App\Entity\Message;
use App\Message\SendMessage;
use App\Message\SendMessageHandler;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SendMessageHandlerTest extends KernelTestCase
{
    public function test_handle_send_message(): void
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        $messageRepository = $entityManager->getRepository(Message::class);
        $this->assertInstanceOf(MessageRepository::class, $messageRepository);

        $text = 'Hello, test me!';
        $sendMessage = new SendMessage($text);

        $handler = new SendMessageHandler($entityManager);
        $handler($sendMessage);

        $message = $messageRepository->findOneBy(
            ['text' => $text],
            ['id' => 'DESC']
        );

        $this->assertInstanceOf(Message::class, $message);
        $this->assertEquals(MessageStatus::SENT, $message->getStatus());
        $this->assertNotNull($message->getCreatedAt());
    }
}