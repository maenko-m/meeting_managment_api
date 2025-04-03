<?php

namespace App\Controller;

use App\DTO\MeetingRoomCreateDTO;
use App\DTO\MeetingRoomUpdateDTO;
use App\Interface\MeetingRoomServiceInterface;
use Nelmio\ApiDocBundle\Attribute\Security;
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
use OpenApi\Attributes as OA;

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


    #[OA\Tag(name: 'Meeting Rooms')]
    #[OA\Get(description: 'Возвращает список переговорных комнат. Поддерживает фильтры. Этот маршрут требует авторизации', summary: 'Получить список переговорных комнат')]
    #[OA\Parameter(
        name: 'office_id',
        description: 'Id офиса',
        in: 'query',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Parameter(
        name: 'name',
        description: 'Название комнаты (поиск подстроки в названии)',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'is_active',
        description: 'Статус комнаты (true - только активные, false/empty - все)',
        in: 'query',
        schema: new OA\Schema(type: 'boolean')
    )]
    #[OA\Parameter(
        name: 'can_access',
        description: 'Доступность для текущего пользователя (true - только доступные, false/empty - все)',
        in: 'query',
        schema: new OA\Schema(type: 'boolean')
    )]
    #[OA\Parameter(
        name: 'page',
        description: 'Страница (1 по умолчанию)',
        in: 'query',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Parameter(
        name: 'limit',
        description: 'Количество строк (10 по умолчанию)',
        in: 'query',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Список переговорных комнат',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', description: 'Id комнаты', type: 'integer', default: '0'),
                    new OA\Property(property: 'name', description: 'Название комнаты', type: 'string', default: 'комната1'),
                    new OA\Property(property: 'size', description: 'Размер комнаты', type: 'integer', default: '0'),
                    new OA\Property(property: 'status', description: 'Статус комнаты', type: 'string', default: 'status'),
                    new OA\Property(property: 'office', description: 'Офис', type: 'object', default: 'object'),
                    new OA\Property(property: 'access', description: 'Доступность для текущего пользователя', type: 'boolean', default: 'true'),
                    new OA\Property(property: 'isPublic', description: 'Публичность комнаты', type: 'boolean', default: 'true'),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Неавторизованный доступ',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', description: 'Описание ошибки', type: 'string', default: 'error message'),
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Токен отсутствует или невалиден',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', description: 'Описание ошибки', type: 'string', default: 'error message'),
            ]
        )
    )]
    #[Security(name: "JwtAuth")]
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
                'photoPath',
                'events',
                'meetingRooms',
                'time_zone'
            ]
        ]);
    }


    #[OA\Tag(name: 'Meeting Rooms')]
    #[OA\Get(description: 'Возвращает переговорную комнату. Этот маршрут требует авторизации', summary: 'Получить переговорную комнату по id')]
    #[OA\Parameter(
        name: 'id',
        description: 'Id комнаты',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Данные переговорной комнаты',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', description: 'Id комнаты', type: 'integer', default: '0'),
                new OA\Property(property: 'name', description: 'Название комнаты', type: 'string', default: 'комната1'),
                new OA\Property(property: 'size', description: 'Размер комнаты', type: 'integer', default: '0'),
                new OA\Property(property: 'status', description: 'Статус комнаты', type: 'string', default: 'status'),
                new OA\Property(property: 'office', description: 'Офис', type: 'object', default: 'object'),
                new OA\Property(property: 'employees', description: 'Массив работников для этой комнаты (если приватная)', type: 'object', default: '[]'),
                new OA\Property(property: 'access', description: 'Доступность для текущего пользователя', type: 'boolean', default: 'true'),
                new OA\Property(property: 'isPublic', description: 'Публичность комнаты', type: 'boolean', default: 'true'),
                new OA\Property(property: 'calendarCode', description: 'Код календаря (Яндекс Календарь)', type: 'integer', default: '0'),
                new OA\Property(property: 'photoPath', description: 'Путь к фото', type: 'string', default: 'path'),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Сущность не найдена',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', description: 'Описание ошибки', type: 'string', default: 'error message'),
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Токен отсутствует или невалиден или ошибка в вводимых данных',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', description: 'Описание ошибки', type: 'string', default: 'error message'),
            ]
        )
    )]
    #[Security(name: "JwtAuth")]
    #[Route('/{id}', name: 'app_meeting_room_by_id', methods: ['GET'], format: 'json')]
    public function getById(int $id): JsonResponse
    {
        $user = $this->getUser();
        $room = $this->meetingRoomService->getMeetingRoomById($id, $user);

        if (!$room) {
            return $this->json(['error' => 'Meeting room not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($room, context: [
            AbstractNormalizer::IGNORED_ATTRIBUTES => [
                'meetingRooms',
                'time_zone'
            ]
        ]);
    }


    #[OA\Tag(name: 'Meeting Rooms')]
    #[OA\Post(description: 'Добавляет новую переговорную комнату. Этот маршрут требует прав модератора', summary: 'Создать переговорную комнату')]
    #[OA\RequestBody(
        description: 'Данные для создания новой комнаты',
        content: new OA\JsonContent(
            ref: '#/components/schemas/MeetingRoomCreateDTO'
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Комната успешно создана',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', description: 'Успешность операции', type: 'boolean'),
                new OA\Property(property: 'id', description: 'Id офиса', type: 'integer', default: '0'),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Сущность не найдена',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', description: 'Описание ошибки', type: 'string', default: 'error message'),
            ]
        )
    )]
    #[OA\Response(
        response: 403,
        description: 'Недостаточно прав',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', description: 'Описание ошибки', type: 'string', default: 'error message'),
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Токен отсутствует или невалиден или ошибка в вводимых данных',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', description: 'Описание ошибки', type: 'string', default: 'error message'),
            ]
        )
    )]
    #[Security(name: "JwtAuth")]
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


    #[OA\Tag(name: 'Meeting Rooms')]
    #[OA\Patch(description: 'Обновляет данные переговорной комнаты полученной по id. Этот маршрут требует прав модератора', summary: 'Изменить переговорную комнату')]
    #[OA\Parameter(
        name: 'id',
        description: 'Id комнаты',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        description: 'Данные для изменения комнаты',
        content: new OA\JsonContent(
            ref: '#/components/schemas/MeetingRoomUpdateDTO'
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Комната успешно обновлена',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', description: 'Успешность операции', type: 'boolean'),
                new OA\Property(property: 'id', description: 'Id офиса', type: 'integer', default: '0'),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Сущность не найдена',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', description: 'Описание ошибки', type: 'string', default: 'error message'),
            ]
        )
    )]
    #[OA\Response(
        response: 403,
        description: 'Недостаточно прав',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', description: 'Описание ошибки', type: 'string', default: 'error message'),
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Токен отсутствует или невалиден или ошибка в вводимых данных',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', description: 'Описание ошибки', type: 'string', default: 'error message'),
            ]
        )
    )]
    #[Security(name: "JwtAuth")]
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


    #[OA\Tag(name: 'Meeting Rooms')]
    #[OA\Delete(description: 'Удаляет переговорную комнату полученную по id. Этот маршрут требует прав модератора', summary: 'Удалить переговорную комнату')]
    #[OA\Parameter(
        name: 'id',
        description: 'Id комнаты',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Комната успешно удалена',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', description: 'Успешность операции', type: 'boolean'),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Сущность не найдена',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', description: 'Описание ошибки', type: 'string', default: 'error message'),
            ]
        )
    )]
    #[OA\Response(
        response: 403,
        description: 'Недостаточно прав',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', description: 'Описание ошибки', type: 'string', default: 'error message'),
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Токен отсутствует или невалиден или ошибка в вводимых данных',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', description: 'Описание ошибки', type: 'string', default: 'error message'),
            ]
        )
    )]
    #[Security(name: "JwtAuth")]
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