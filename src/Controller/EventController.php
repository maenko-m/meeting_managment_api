<?php

namespace App\Controller;

use App\DTO\EventCreateDTO;
use App\DTO\EventUpdateDTO;
use App\Interface\EventServiceInterface;
use Nelmio\ApiDocBundle\Attribute\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;


#[Route('/api/event')]
final class EventController extends AbstractController {
    private EventServiceInterface $eventService;
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator, EventServiceInterface $eventService)
    {
        $this->validator = $validator;
        $this->eventService = $eventService;
    }

    #[OA\Tag(name: 'Events')]
    #[OA\Get(description: 'Возвращает список мероприятий. Поддерживает фильтры. Этот маршрут требует авторизации', summary: 'Получить список мероприятий')]
    #[OA\Parameter(
        name: 'room_id',
        description: 'Id переговорной комнаты',
        in: 'query',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Parameter(
        name: 'name',
        description: 'Название мероприятия (поиск подстроки в названии)',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'type',
        description: 'Тип мероприятия',
        in: 'query',
        schema: new OA\Schema(
            type: 'string',
            enum: ['участник', 'организатор'],
            example: null
        )
    )]
    #[OA\Parameter(
        name: 'desc_order',
        description: 'Обратная сортировка по дате (true - по убыванию, false/empty - по возрастанию)',
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
        description: 'Список мероприятий',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', description: 'Id мероприятия', type: 'integer', default: '0'),
                    new OA\Property(property: 'name', description: 'Название мероприятия', type: 'string', default: 'комната1'),
                    new OA\Property(property: 'description', description: 'Описание мероприятия', type: 'string', default: 'описание'),
                    new OA\Property(property: 'date', description: 'Дата мероприятия', type: 'date', default: 'YYYY-MM-DD'),
                    new OA\Property(property: 'author', description: 'Автор мероприятия', type: 'object', default: 'object'),
                    new OA\Property(property: 'employees', description: 'Список участников', type: 'object', default: 'object'),
                    new OA\Property(property: 'timeStart', description: 'Время начала', type: 'date', default: 'H:i:s'),
                    new OA\Property(property: 'timeEnd', description: 'Время конца', type: 'date', default: 'H:i:s'),
                    new OA\Property(property: 'meetingRoomName', description: 'Название переговорной комнаты', type: 'string', default: 'name'),
                    new OA\Property(property: 'meetingRoomId', description: 'Id переговорной комнаты', type: 'integer', default: '0'),
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
    #[Route(name: 'app_event_get_all', methods: ['GET'], format: 'json')]
    public function getAll(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $filters = $request->query->all();

        $events = $this->eventService->getAllEvents($filters, $user);

        return $this->json($events);
    }


    #[OA\Tag(name: 'Events')]
    #[OA\Get(description: 'Возвращает список мероприятий по дате. Этот маршрут требует авторизации', summary: 'Получить список мероприятий по дате')]
    #[OA\Parameter(
        name: 'date',
        description: 'Дата мероприятия (формат: YYYY-MM-DD)',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'string', format: 'date'),
        example: '2025-01-01'
    )]
    #[OA\Response(
        response: 200,
        description: 'Список мероприятий',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', description: 'Id мероприятия', type: 'integer', default: '0'),
                    new OA\Property(property: 'name', description: 'Название мероприятия', type: 'string', default: 'комната1'),
                    new OA\Property(property: 'description', description: 'Описание мероприятия', type: 'string', default: 'описание'),
                    new OA\Property(property: 'date', description: 'Дата мероприятия', type: 'date', default: 'YYYY-MM-DD'),
                    new OA\Property(property: 'timeStart', description: 'Время начала', type: 'date', default: 'H:i:s'),
                    new OA\Property(property: 'timeEnd', description: 'Время конца', type: 'date', default: 'H:i:s'),
                    new OA\Property(property: 'meetingRoomName', description: 'Название переговорной комнаты', type: 'string', default: 'name'),
                    new OA\Property(property: 'meetingRoomId', description: 'Id переговорной комнаты', type: 'integer', default: '0'),
                ]
            )
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
    #[Route('/date/{date}', name: 'app_event_get_all_by_date', methods: ['GET'], format: 'json')]
    public function getAllByDate(string $date): JsonResponse
    {
        try {
            $dateObj = new \DateTime($date);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Invalid date format. Use YYYY-MM-DD.'], Response::HTTP_BAD_REQUEST);
        }

        $events = $this->eventService->getAllEventsByDate($dateObj);

        return $this->json($events, context: [
            AbstractNormalizer::IGNORED_ATTRIBUTES => [
                'author',
                'employees',
            ]
        ]);
    }


    #[OA\Tag(name: 'Events')]
    #[OA\Get(description: 'Возвращает мероприятие. Этот маршрут требует авторизации', summary: 'Получить мероприятие по id')]
    #[OA\Parameter(
        name: 'id',
        description: 'Id мероприятия',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Данные мероприятия',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', description: 'Id мероприятия', type: 'integer', default: '0'),
                new OA\Property(property: 'name', description: 'Название мероприятия', type: 'string', default: 'комната1'),
                new OA\Property(property: 'description', description: 'Описание мероприятия', type: 'string', default: 'описание'),
                new OA\Property(property: 'date', description: 'Дата мероприятия', type: 'date', default: 'YYYY-MM-DD'),
                new OA\Property(property: 'author', description: 'Автор мероприятия', type: 'object', default: 'object'),
                new OA\Property(property: 'employees', description: 'Список участников', type: 'object', default: 'object'),
                new OA\Property(property: 'timeStart', description: 'Время начала', type: 'date', default: 'H:i:s'),
                new OA\Property(property: 'timeEnd', description: 'Время конца', type: 'date', default: 'H:i:s'),
                new OA\Property(property: 'meetingRoomName', description: 'Название переговорной комнаты', type: 'string', default: 'name'),
                new OA\Property(property: 'meetingRoomId', description: 'Id переговорной комнаты', type: 'integer', default: '0'),
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
    #[Route('/{id}', name: 'app_event_get_by_id', methods: ['GET'], format: 'json')]
    public function getById(int $id): JsonResponse
    {
        $event = $this->eventService->getEventById($id);

        if (!$event) {
            return $this->json(['error' => 'Event not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($event);
    }


    #[OA\Tag(name: 'Events')]
    #[OA\Post(description: 'Добавляет новое мероприятие. Этот маршрут требует авторизации', summary: 'Создать мероприятие')]
    #[OA\RequestBody(
        description: 'Данные для создания нового мероприятия',
        content: new OA\JsonContent(
            ref: '#/components/schemas/EventCreateDTO'
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Мероприятие успешно создано',
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
        response: 400,
        description: 'Токен отсутствует или невалиден или ошибка в вводимых данных',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', description: 'Описание ошибки', type: 'string', default: 'error message'),
            ]
        )
    )]
    #[Security(name: "JwtAuth")]
    #[Route(name: 'app_event_add', methods: ['POST'], format: 'json')]
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


    #[OA\Tag(name: 'Events')]
    #[OA\Patch(description: 'Обновляет данные мероприятия полученного по id. Этот маршрут требует прав модератора', summary: 'Изменить мероприятие')]
    #[OA\Parameter(
        name: 'id',
        description: 'Id мероприятия',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        description: 'Данные для обновления мероприятия',
        content: new OA\JsonContent(
            ref: '#/components/schemas/EventUpdateDTO'
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Мероприятие успешно обновлено',
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

    #[OA\Tag(name: 'Events')]
    #[OA\Delete(description: 'Удаляет мероприятие полученное по id. Этот маршрут требует прав модератора', summary: 'Удалить мероприятие')]
    #[OA\Parameter(
        name: 'id',
        description: 'Id мероприятия',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Мероприятие успешно удалена',
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
