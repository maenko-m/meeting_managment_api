<?php


namespace App\Controller;

use App\DTO\EmployeeCreateDTO;
use App\DTO\EmployeeUpdateDTO;
use App\Entity\Employee;
use App\Interface\EmployeeServiceInterface;
use Nelmio\ApiDocBundle\Attribute\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;

#[Route('/api/employee')]
final class EmployeeController extends AbstractController
{
    private EmployeeServiceInterface $employeeService;
    private ValidatorInterface $validator;

    public function __construct(EmployeeServiceInterface $employeeService, ValidatorInterface $validator)
    {
        $this->validator = $validator;
        $this->employeeService = $employeeService;
    }


    #[OA\Tag(name: 'Employees')]
    #[OA\Get(description: 'Возвращает список сотрудников. Этот маршрут требует авторизации', summary: 'Получить список работников')]
    #[OA\Response(
        response: 200,
        description: 'Список сотрудников',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', description: 'Id сотрудника', type: 'integer', default: '0'),
                    new OA\Property(property: 'email', description: 'email', type: 'string', default: 'email@email.com'),
                    new OA\Property(property: 'fullName', description: 'ФИО сотрудника', type: 'string', default: 'Иванов Иван Иванович'),
                    new OA\Property(property: 'organization', description: 'Данные организации', type: 'object', default: 'object')
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
    #[Route(name: 'api_employee_get_all', methods: ['GET'], format: 'json')]
    public function getAll(): JsonResponse
    {
        $user = $this->getUser();

        $employees = $this->employeeService->getAllEmployees($user);

        return $this->json($employees, Response::HTTP_OK);
    }

    #[Route('/self', name: 'api_employee_get_self', methods: ['GET'], format: 'json')]
    public function getSelf(): JsonResponse
    {
        $user = $this->getUser();
        if ($user instanceof Employee) {
            return $this->json([
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getName(),
                'surname' => $user->getSurname(),
                'patronymic' => $user->getPatronymic(),
                'roles' => $user->getRoles(),
            ], Response::HTTP_OK);
        }
        return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
    }

    #[OA\Tag(name: 'Employees')]
    #[OA\Get(description: 'Возвращает сотрудника. Этот маршрут требует авторизации', summary: 'Получить работника по id')]
    #[OA\Parameter(
        name: 'id',
        description: 'Id сотрудника',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Данные сотрудника',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', description: 'Id сотрудника', type: 'integer', default: '0'),
                new OA\Property(property: 'email', description: 'email', type: 'string', default: 'email@email.com'),
                new OA\Property(property: 'fullName', description: 'ФИО сотрудника', type: 'string', default: 'Иванов Иван Иванович'),
                new OA\Property(property: 'organization', description: 'Данные организации', type: 'object', default: 'object')
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Работник не найден',
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
    #[Route('/{id}', name: 'api_employee_get_by_id', methods: ['GET'], format: 'json')]
    public function getById(int $id): JsonResponse
    {
        $employee = $this->employeeService->getEmployeeById($id);

        if (!$employee) {
            return new JsonResponse(['error' => 'Employee not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($employee, Response::HTTP_OK);
    }

    #[OA\Tag(name: 'Employees')]
    #[OA\Post(description: 'Добавляет нового пользователя-работника', summary: 'Создать работника')]
    #[OA\RequestBody(
        description: 'Данные для создания нового сотрудника',
        content: new OA\JsonContent(
            ref: '#/components/schemas/EmployeeCreateDTO'
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Сотрудник успешно создан',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', description: 'Успешность операции', type: 'boolean'),
                new OA\Property(property: 'email', description: 'Email сотрудника', type: 'string', default: 'email@email.com'),
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
    #[Route('/register', name: 'api_employee_register', methods: ['POST'], format: 'json')]
    public function register(#[MapRequestPayload] EmployeeCreateDTO $employeeCreateDTO): JsonResponse
    {
        $errors = $this->validator->validate($employeeCreateDTO);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string)$errors], Response::HTTP_BAD_REQUEST);
        }

        try {
            $employee = $this->employeeService->createEmployee($employeeCreateDTO);

            return $this->json([
                'success' => true,
                'email' => $employee->getEmail(),
            ], Response::HTTP_CREATED);
        }
        catch (HttpException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }


    #[OA\Tag(name: 'Employees')]
    #[OA\Patch(description: 'Обновляет данные сотрудника полученного по id. Этот маршрут требует права модератора при обращении к другим сотрудникам', summary: 'Изменить работника')]
    #[OA\RequestBody(
        description: 'Данные для изменения сотрудника',
        content: new OA\JsonContent(
            ref: '#/components/schemas/EmployeeUpdateDTO'
        )
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Id сотрудника',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Сотрудник успешно обновлен',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', description: 'Успешность операции', type: 'boolean'),
                new OA\Property(property: 'email', description: 'Email сотрудника', type: 'string', default: 'email@email.com')
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
    #[Route('/{id}', name: 'api_employee_edit', methods: ['PATCH'], format: 'json')]
    public function edit(int $id, #[MapRequestPayload] EmployeeUpdateDTO $employeeUpdateDTO): JsonResponse
    {
        $errors = $this->validator->validate($employeeUpdateDTO);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string)$errors], Response::HTTP_BAD_REQUEST);
        }

        $employee = $this->employeeService->getEmployeeById($id);

        if ($this->getUser()->getUserIdentifier() !== $employee->getEmail() && !$this->isGranted('ROLE_MODERATOR')) {
            return $this->json(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        try {
            $employee = $this->employeeService->updateEmployee($id, $employeeUpdateDTO);

            return $this->json([
                'success' => true,
                'email' => $employee->getEmail(),
            ], Response::HTTP_OK);
        }
        catch (HttpException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }


    #[OA\Tag(name: 'Employees')]
    #[OA\Delete(description: 'Удаляет сотрудника полученного по id. Этот маршрут требует права модератора', summary: 'Удалить работника')]
    #[OA\Parameter(
        name: 'id',
        description: 'Id сотрудника',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Сотрудник успешно удален',
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
    #[Route('/{id}', name: 'api_employee_delete', methods: ['DELETE'], format: 'json')]
    #[IsGranted('ROLE_MODERATOR')]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->employeeService->deleteEmployee($id);

            return $this->json(['success' => true,], Response::HTTP_OK);
        }
        catch (HttpException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }
}
