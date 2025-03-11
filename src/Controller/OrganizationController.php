<?php

namespace App\Controller;

use App\Interface\OrganizationServiceInterface;
use App\DTO\OrganizationCreateDTO;
use App\DTO\OrganizationUpdateDTO;
use Nelmio\ApiDocBundle\Attribute\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/organization')]
final class OrganizationController extends AbstractController
{
    private OrganizationServiceInterface $organizationService;
    private ValidatorInterface $validator;

    public function __construct(OrganizationServiceInterface $organizationService, ValidatorInterface $validator)
    {
        $this->organizationService = $organizationService;
        $this->validator = $validator;
    }


    #[OA\Tag(name: 'Organizations')]
    #[OA\Get(description: 'Возвращает список организаций. Этот маршрут требует авторизации', summary: 'Получить список организаций')]
    #[OA\Response(
        response: 200,
        description: 'Список организаций',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', description: 'Id организации', type: 'integer', default: '0'),
                    new OA\Property(property: 'name', description: 'Название организации', type: 'string', default: 'название'),
                    new OA\Property(property: 'status', description: 'Статус организации', type: 'string', default: 'status'),
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
    #[Route(name: 'app_organization_get_all', methods: ['GET'], format: 'json')]
    public function getAll(): JsonResponse
    {
        return $this->json($this->organizationService->getAllOrganizations());
    }


    #[OA\Tag(name: 'Organizations')]
    #[OA\Get(description: 'Возвращает организацию. Этот маршрут требует авторизации', summary: 'Получить организацию по id')]
    #[OA\Parameter(
        name: 'id',
        description: 'Id сотрудника',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Данные организации',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', description: 'Id организации', type: 'integer', default: '0'),
                new OA\Property(property: 'name', description: 'Название организации', type: 'string', default: 'название'),
                new OA\Property(property: 'status', description: 'Статус организации', type: 'string', default: 'status'),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Организация не найдена',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', description: 'Описание ошибки', type: 'string', default: 'error message'),
            ]
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
    #[Route('/{id}', name: 'app_organization_get_by_id', methods: ['GET'], format: 'json')]
    public function getById(int $id): JsonResponse
    {
        $organization = $this->organizationService->getOrganizationById($id);

        if (!$organization) {
            return $this->json(['error' => 'Organization not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($organization);
    }


    #[OA\Tag(name: 'Organizations')]
    #[OA\Post(description: 'Добавляет новую организацию. Этот маршрут требует прав модератора', summary: 'Создать организацию')]
    #[OA\RequestBody(
        description: 'Данные для создания нового сотрудника',
        content: new OA\JsonContent(
            ref: '#/components/schemas/OrganizationCreateDTO'
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Организация успешно создана',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', description: 'Успешность операции', type: 'boolean'),
                new OA\Property(property: 'id', description: 'Id организации', type: 'integer', default: '0'),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Организация не найдена',
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
        description: 'Токен отсутствует или невалиден или ошибка в вводимых данных',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', description: 'Описание ошибки', type: 'string', default: 'error message'),
            ]
        )
    )]
    #[Security(name: "JwtAuth")]
    #[Route(name: 'app_organization_add', methods: ['POST'], format: 'json')]
    #[IsGranted('ROLE_MODERATOR')]
    public function add(#[MapRequestPayload] OrganizationCreateDTO $organizationCreateDTO): JsonResponse
    {
        $errors = $this->validator->validate($organizationCreateDTO);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string)$errors], Response::HTTP_BAD_REQUEST);
        }

        try {
            $organization = $this->organizationService->createOrganization($organizationCreateDTO);

            return $this->json([
                'success' => true,
                'id' => $organization->getId(),
            ], Response::HTTP_CREATED);
        }
        catch (HttpException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }


    #[OA\Tag(name: 'Organizations')]
    #[OA\Patch(description: 'Обновляет данные организации полученной по id. Этот маршрут требует прав модератора', summary: 'Изменить организацию')]
    #[OA\Parameter(
        name: 'id',
        description: 'Id организации',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        description: 'Данные для изменения организации',
        content: new OA\JsonContent(
            ref: '#/components/schemas/OrganizationUpdateDTO'
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Организация успешно обновлена',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', description: 'Успешность операции', type: 'boolean'),
                new OA\Property(property: 'id', description: 'Id организации', type: 'integer', default: '0'),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Организация не найдена',
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
        description: 'Токен отсутствует или невалиден или ошибка в вводимых данных',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', description: 'Описание ошибки', type: 'string', default: 'error message'),
            ]
        )
    )]
    #[Security(name: "JwtAuth")]
    #[Route('/{id}', name: 'app_organization_edit', methods: ['PATCH'], format: 'json')]
    #[IsGranted('ROLE_MODERATOR')]
    public function edit(int $id, #[MapRequestPayload] OrganizationUpdateDTO $organizationUpdateDTO): JsonResponse
    {
        $errors = $this->validator->validate($organizationUpdateDTO);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string)$errors], Response::HTTP_BAD_REQUEST);
        }

        try {
            $organization = $this->organizationService->updateOrganization($id, $organizationUpdateDTO);

            return $this->json([
                'success' => true,
                'id' => $organization->getId(),
            ], Response::HTTP_OK);
        }
        catch (HttpException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }

    #[OA\Tag(name: 'Organizations')]
    #[OA\Delete(description: 'Удаляет организацию полученную по id. Этот маршрут требует прав модератора', summary: 'Удалить организацию')]
    #[OA\Parameter(
        name: 'id',
        description: 'Id организации',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Организация успешно удалена',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', description: 'Успешность операции', type: 'boolean'),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Организация не найдена',
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
        description: 'Токен отсутствует или невалиден или ошибка в вводимых данных',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', description: 'Описание ошибки', type: 'string', default: 'error message'),
            ]
        )
    )]
    #[Security(name: "JwtAuth")]
    #[Route('/{id}', name: 'app_organization_delete', methods: ['DELETE'], format: 'json')]
    #[IsGranted('ROLE_MODERATOR')]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->organizationService->deleteOrganization($id);

            return $this->json(['success' => true,], Response::HTTP_OK);
        }
        catch (HttpException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }
}
