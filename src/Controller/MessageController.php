<?php
declare(strict_types=1);

namespace App\Controller;

use App\Config\MessageStatus;
use App\Entity\Message;
use App\Message\SendMessage;
use App\Repository\MessageRepository;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('api/messages')]
class MessageController extends AbstractController
{
    public function __construct(
        protected readonly MessageRepository $messageRepository,
        protected readonly MessageBusInterface $bus
    ) {
    }

    /**
     * TODO: cover this method with tests, and refactor the code (including other files that need to be refactored)
     */
    #[Route('/', methods: [Request::METHOD_GET])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Returns list of all messages or filtered by status.',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(
                type: Message::class,
                groups: ['message:read']
            ))
        )
    )]
    #[OA\Parameter(
        name: 'status',
        description: 'The status of the message.',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'string',
            enum: MessageStatus::class
        )
    )]
    public function list(Request $request): JsonResponse
    {
        $status = $request->query->getEnum('status', MessageStatus::class);
        $messages = $this->messageRepository->findByStatus($status);

        return $this->json(
            $messages,
            context: ['groups' => 'message:read']
        );
    }

    #[Route('/send', methods: [Request::METHOD_POST])]
    #[OA\Response(
        response: Response::HTTP_NO_CONTENT,
        description: 'Message sent.',
    )]
    #[OA\RequestBody(
        content: new Model(
            type: Message::class,
            groups: ['message:send']
        )
    )]
    public function send(Request $request): JsonResponse
    {
        $text = $request->getPayload()->getString('text');

        if ('' === $text) {
            return $this->json(['error' => '`text` is required'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->bus->dispatch(new SendMessage($text));

        return $this->json('Successfully sent', Response::HTTP_NO_CONTENT);
    }
}
