<?php

namespace App\Controller;

use App\DTO\MeetingRoomCreateDTO;
use App\DTO\MeetingRoomUpdateDTO;
use App\Interface\MeetingRoomServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/meeting-room')]
final class MeetingRoomController extends AbstractController
{
    private MeetingRoomServiceInterface $meetingRoomService;
    private ValidatorInterface $validator;

    public function __construct(MeetingRoomServiceInterface $meetingRoomService, ValidatorInterface $validator)
    {
        $this->meetingRoomService = $meetingRoomService;
        $this->validator = $validator;
    }

    #[Route(name: 'app_meeting_room_get_all', methods: ['GET'])]
    public function getAll(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $filter = $request->query->all();
        $rooms = $this->meetingRoomService->getAllMeetingRooms($filter, $user);
        return $this->json($rooms, context: [
            AbstractNormalizer::IGNORED_ATTRIBUTES => [
                'calendarCode',
                'description',
                'employees',
                'photoPath',
                'events',
            ]
        ]);
    }

    #[Route('/{id}', name: 'app_meeting_room_by_id', methods: ['GET'], format: 'json')]
    public function getById(int $id): JsonResponse
    {
        $user = $this->getUser();
        $room = $this->meetingRoomService->getMeetingRoomById($id, $user);

        if (!$room) {
            return $this->json(['error' => 'Meeting room not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($room);
    }

    #[Route(name: 'app_meeting_room_add', methods: ['POST'], format: 'json')]
    #[IsGranted('ROLE_MODERATOR')]
    public function add(#[MapRequestPayload] MeetingRoomCreateDTO $meetingRoomCreateDTO): JsonResponse
    {
        $errors = $this->validator->validate($meetingRoomCreateDTO);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string)$errors], Response::HTTP_BAD_REQUEST);
        }

        try {
            $meetingRoom = $this->meetingRoomService->createMeetingRoom($meetingRoomCreateDTO);

            return $this->json([
                'success' => true,
                'id' => $meetingRoom->getId(),
            ], Response::HTTP_CREATED);
        }
        catch (HttpException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }

    #[Route('/{id}', name: 'app_meeting_room_edit', methods: ['PATCH'], format: 'json')]
    #[IsGranted('ROLE_MODERATOR')]
    public function edit(int $id, #[MapRequestPayload] MeetingRoomUpdateDTO $meetingRoomUpdateDTO): JsonResponse
    {
        $errors = $this->validator->validate($meetingRoomUpdateDTO);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string)$errors], Response::HTTP_BAD_REQUEST);
        }

        try {
            $meetingRoom = $this->meetingRoomService->updateMeetingRoom($id, $meetingRoomUpdateDTO);

            return $this->json([
                'success' => true,
                'id' => $meetingRoom->getId(),
            ], Response::HTTP_CREATED);
        }
        catch (HttpException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }

    #[Route('/{id}', name: 'app_meeting_room_delete', methods: ['DELETE'], format: 'json')]
    #[IsGranted('ROLE_MODERATOR')]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->meetingRoomService->deleteMeetingRoom($id);

            return $this->json(['success' => true,], Response::HTTP_OK);
        }
        catch (HttpException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }
}