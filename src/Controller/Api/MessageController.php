<?php
// src/Controller/Api/MessageController.php
namespace App\Controller\Api;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use App\Repository\ConversationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/messages')]
class MessageController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security,
        private SerializerInterface $serializer,
        private ConversationRepository $conversationRepository
    ) {}

    #[Route('', name: 'api_messages_list', methods: ['GET'])]
    public function listConversations(): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

        // Récupère toutes les conversations de l'utilisateur
        $conversations = $this->conversationRepository->findUserConversations($currentUser);

        $data = $this->serializer->serialize($conversations, 'json', ['groups' => 'conversation_list']);

        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/{conversationId}', name: 'api_messages_create', methods: ['POST'])]
    public function createMessage(Request $request, int $conversationId): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

        $data = json_decode($request->getContent(), true);

        // Trouver la conversation
        $conversation = $this->conversationRepository->find($conversationId);
        if (!$conversation) {
            return new JsonResponse(['error' => 'Conversation not found'], 404);
        }

        // Vérifier que l'utilisateur fait partie de la conversation
        if ($conversation->getUser1()->getId() !== $currentUser->getId() && 
            $conversation->getUser2()->getId() !== $currentUser->getId()) {
            return new JsonResponse(['error' => 'Unauthorized access to conversation'], 403);
        }

        $message = new Message();
        $message->setContent($data['content']);
        $message->setSender($currentUser);
        $message->setConversation($conversation);
        $message->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($message);
        
        // Mettre à jour la date de modification de la conversation
        $conversation->setUpdatedAt(new \DateTimeImmutable());
        
        $this->em->flush();

        return new JsonResponse(['status' => 'Message sent'], 201);
    }

    #[Route('/conversation/{userId}', name: 'api_messages_conversation_create', methods: ['POST'])]
    public function createConversation(Request $request, int $userId): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

        $otherUser = $this->em->getRepository(User::class)->find($userId);
        if (!$otherUser) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        // Vérifier si une conversation existe déjà
        $existingConversation = $this->conversationRepository->findConversationByUsers($currentUser, $otherUser);
        
        if ($existingConversation) {
            return new JsonResponse([
                'conversation_id' => $existingConversation->getId(),
                'status' => 'Conversation already exists'
            ], 200);
        }

        // Créer une nouvelle conversation
        $conversation = new Conversation();
        $conversation->setUser1($currentUser);
        $conversation->setUser2($otherUser);
        
        $this->em->persist($conversation);
        $this->em->flush();

        return new JsonResponse([
            'conversation_id' => $conversation->getId(),
            'status' => 'Conversation created'
        ], 201);
    }

    #[Route('/conversation/{conversationId}', name: 'api_messages_conversation', methods: ['GET'])]
    public function getConversationMessages(int $conversationId): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

        $conversation = $this->conversationRepository->find($conversationId);
        if (!$conversation) {
            return new JsonResponse(['error' => 'Conversation not found'], 404);
        }

        // Vérifier que l'utilisateur fait partie de la conversation
        if ($conversation->getUser1()->getId() !== $currentUser->getId() && 
            $conversation->getUser2()->getId() !== $currentUser->getId()) {
            return new JsonResponse(['error' => 'Unauthorized access to conversation'], 403);
        }

        $messages = $this->em->getRepository(Message::class)->findMessagesByConversation($conversationId);

        $data = $this->serializer->serialize([
            'conversation' => $conversation,
            'messages' => $messages
        ], 'json', ['groups' => ['conversation_details', 'message_details']]);

        return new JsonResponse($data, 200, [], true);
    }
}