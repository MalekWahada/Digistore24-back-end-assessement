<?php

declare(strict_types=1);

namespace App\Tests\Message;

use App\Config\MessageStatus;
use App\Entity\Message;
use App\Message\SendMessage;
use App\Message\SendMessageHandler;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Uuid;

class SendMessageHandlerTest extends KernelTestCase
{
    public function test_send_message(): void
    {
        $mockEntityManager = $this->createMock(EntityManagerInterface::class);
        $handler = new SendMessageHandler($mockEntityManager);

        $text = 'Test message content';
        $sendMessage = new SendMessage($text);

//        $mockEntityManager
//            ->expects($this->any())
//            ->method('persist')
//            ->with($this->callback(function (Message $message) use ($sendMessage) {
//                return $message->getText() === $sendMessage->text &&
//                    $message->getStatus() === MessageStatus::SENT &&
//                    Uuid::isValid($message->getUuid());
//            }));
//
//        $mockEntityManager
//            ->expects($this->any())
//            ->method('flush');

        $handler($sendMessage);

        /** @var MessageRepository|ContainerInterface $messageRepository */
        $messageRepository = self::getContainer()->get(MessageRepository::class);

        $message = $messageRepository->findOneBy(
            ['text' => $text],
            ['id' => 'DESC']
        );

        $this->assertInstanceOf(Message::class, $message);
        $this->assertNotNull($message->getCreatedAt());
    }
}