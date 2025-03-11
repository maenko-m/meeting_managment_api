<?php

namespace App\Controller;

use App\DTO\EventCreateDTO;
use App\DTO\EventUpdateDTO;
use App\Entity\Event;
use App\Interface\EventServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;


#[Route('/api/event')]
final class EventController extends AbstractController {
    private EventServiceInterface $eventService;
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator, EventServiceInterface $eventService)
    {
        $this->validator = $validator;
        $this->eventService = $eventService;
    }

    #[Route(name: 'app_event_get_all', methods: ['GET'], format: 'json')]
    public function getAll(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $filters = $request->query->all();

        $events = $this->eventService->getAllEvents($filters, $user);

        return $this->json($events);
    }

    #[Route('/{id}', name: 'app_event_get_by_id', methods: ['GET'], format: 'json')]
    public function getById(int $id): JsonResponse
    {
        $event = $this->eventService->getEventById($id);

        if (!$event) {
            return $this->json(['error' => 'Event not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($event);
    }

    #[Route(name: 'app_event_add', methods: ['POST'], format: 'json')]
    #[IsGranted('ROLE_MODERATOR')]
    public function add(#[MapRequestPayload] EventCreateDTO $eventCreateDTO): JsonResponse
    {
        $errors = $this->validator->validate($eventCreateDTO);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string)$errors], Response::HTTP_BAD_REQUEST);
        }

        try {
            $event = $this->eventService->createEvent($eventCreateDTO);

            return $this->json([
                'success' => true,
                'id' => $event->getId(),
            ], Response::HTTP_CREATED);
        }
        catch (HttpException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }

    #[Route('/{id}', name: 'app_event_edit', methods: ['PATCH'], format: 'json')]
    #[IsGranted('ROLE_MODERATOR')]
    public function edit(int $id, #[MapRequestPayload] EventUpdateDTO $eventUpdateDTO): JsonResponse
    {
        $errors = $this->validator->validate($eventUpdateDTO);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string)$errors], Response::HTTP_BAD_REQUEST);
        }

        try {
            $event = $this->eventService->updateEvent($id, $eventUpdateDTO);

            return $this->json([
                'success' => true,
                'id' => $event->getId(),
            ], Response::HTTP_OK);
        }
        catch (HttpException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }

    #[Route('/{id}', name: 'app_event_delete', methods: ['DELETE'], format: 'json')]
    #[IsGranted('ROLE_MODERATOR')]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->eventService->deleteEvent($id);

            return $this->json(['success' => true,], Response::HTTP_OK);
        }
        catch (HttpException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }
}
