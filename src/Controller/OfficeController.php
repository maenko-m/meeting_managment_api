<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\OfficeCreateDTO;
use App\DTO\OfficeUpdateDTO;
use App\Interface\OfficeServiceInterface;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/office')]
final class OfficeController extends AbstractController
{
    private OfficeServiceInterface $officeService;
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator, OfficeServiceInterface $officeService)
    {
        $this->validator = $validator;
        $this->officeService = $officeService;
    }

    #[OA\Tag(name: 'Offices')]
    #[OA\Get(description: 'Возвращает список офисов. Этот маршрут требует прав модератора', summary: 'Получить список офисов')]
    #[OA\Response(
        response: 200,
        description: 'Список офисов',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', description: 'Id офиса', type: 'integer', default: '0'),
                    new OA\Property(property: 'name', description: 'Название офиса', type: 'string', default: 'название'),
                    new OA\Property(property: 'organization', description: 'Данные организации', type: 'object', default: 'object'),
                    new OA\Property(property: 'timeZone', description: 'Часовой пояс', type: 'integer', default: '0'),
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
    #[Route(name: 'app_office_get_all', methods: ['GET'], format: 'json')]
    public function getAll(): JsonResponse
    {
        return $this->json($this->officeService->getAllOffices());
    }


    #[OA\Tag(name: 'Offices')]
    #[OA\Get(description: 'Обновляет данные офиса полученного по id. Этот маршрут требует прав модератора', summary: 'Получить офис по id')]
    #[OA\Parameter(
        name: 'id',
        description: 'Id офиса',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Данные офиса',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', description: 'Id офиса', type: 'integer', default: '0'),
                new OA\Property(property: 'name', description: 'Название офиса', type: 'string', default: 'название'),
                new OA\Property(property: 'organization', description: 'Данные организации', type: 'object', default: 'object'),
                new OA\Property(property: 'timeZone', description: 'Часовой пояс', type: 'integer', default: '0'),
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
    #[Route('/{id}', name: 'app_office_get_by_id', methods: ['GET'], format: 'json')]
    public function getById(int $id): JsonResponse
    {
        $office = $this->officeService->getOfficeById($id);

        if (!$office) {
            return $this->json(['message' => 'Office not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($office);
    }

    #[OA\Tag(name: 'Offices')]
    #[OA\Post(description: 'Добавляет новый офис. Этот маршрут требует авторизации', summary: 'Создать офис')]
    #[OA\RequestBody(
        description: 'Данные для создания нового офиса',
        content: new OA\JsonContent(
            ref: '#/components/schemas/OfficeCreateDTO'
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Офис успешно создан',
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
    #[Route(name: 'app_office_add', methods: ['POST'], format: 'json')]
    #[IsGranted('ROLE_MODERATOR')]
    public function add(#[MapRequestPayload] OfficeCreateDTO $officeCreateDTO): JsonResponse
    {
        $errors = $this->validator->validate($officeCreateDTO);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string)$errors], Response::HTTP_BAD_REQUEST);
        }

        try {
            $office = $this->officeService->createOffice($officeCreateDTO);

            return $this->json([
                'success' => true,
                'id' => $office->getId(),
            ], Response::HTTP_CREATED);
        }
        catch (HttpException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }

    #[OA\Tag(name: 'Offices')]
    #[OA\Patch(description: 'Обновляет данные офиса полученного по id. Этот маршрут требует прав модератор', summary: 'Изменить офис')]
    #[OA\Parameter(
        name: 'id',
        description: 'Id офиса',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        description: 'Данные для изменения офиса',
        content: new OA\JsonContent(
            ref: '#/components/schemas/OfficeUpdateDTO'
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Офис успешно изменен',
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
    #[Route('/{id}', name: 'app_office_edit', methods: ['PATCH'], format: 'json')]
    #[IsGranted('ROLE_MODERATOR')]
    public function edit(int $id, #[MapRequestPayload] OfficeUpdateDTO $officeUpdateDTO): JsonResponse
    {
        $errors = $this->validator->validate($officeUpdateDTO);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string)$errors], Response::HTTP_BAD_REQUEST);
        }

        try {
            $office = $this->officeService->updateOffice($id, $officeUpdateDTO);

            return $this->json([
                'success' => true,
                'id' => $office->getId(),
            ], Response::HTTP_OK);
        }
        catch (HttpException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }


    #[OA\Tag(name: 'Offices')]
    #[OA\Delete(description: 'Удаляет офис полученный по id. Этот маршрут требует прав модератор', summary: 'Удалить офис')]
    #[OA\Parameter(
        name: 'id',
        description: 'Id офиса',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Офис успешно удален',
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
    #[Route('/{id}', name: 'app_office_delete', methods: ['DELETE'], format: 'json')]
    #[IsGranted('ROLE_MODERATOR')]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->officeService->deleteOffice($id);

            return $this->json(['success' => true,], Response::HTTP_OK);
        }
        catch (HttpException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }
}
