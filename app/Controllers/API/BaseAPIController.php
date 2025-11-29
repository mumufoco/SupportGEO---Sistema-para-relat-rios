<?php

namespace App\Controllers\API;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;

class BaseAPIController extends ResourceController
{
    protected $format = 'json';
    protected $modelName;
    protected $repositoryName;

    protected function respondSuccess($data = null, string $message = 'Success', int $code = 200): ResponseInterface
    {
        return $this->respond([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function respondCreated($data = null, string $message = 'Created'): ResponseInterface
    {
        return $this->respondSuccess($data, $message, 201);
    }

    protected function respondError(string $message, int $code = 400, $errors = null): ResponseInterface
    {
        $response = [
            'status' => 'error',
            'message' => $message
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return $this->respond($response, $code);
    }

    protected function respondNotFound(string $message = 'Resource not found'): ResponseInterface
    {
        return $this->respondError($message, 404);
    }

    protected function respondUnauthorized(string $message = 'Unauthorized'): ResponseInterface
    {
        return $this->respondError($message, 401);
    }

    protected function respondForbidden(string $message = 'Forbidden'): ResponseInterface
    {
        return $this->respondError($message, 403);
    }

    protected function respondValidationError(array $errors): ResponseInterface
    {
        return $this->respondError('Validation failed', 422, $errors);
    }

    protected function getUsuarioId(): ?int
    {
        $authHeader = $this->request->getHeaderLine('Authorization');

        if (empty($authHeader)) {
            return null;
        }

        $token = str_replace('Bearer ', '', $authHeader);

        return 1;
    }

    protected function requireAuth(): ?int
    {
        $usuarioId = $this->getUsuarioId();

        if (!$usuarioId) {
            $this->respondUnauthorized()->send();
            exit;
        }

        return $usuarioId;
    }

    protected function validateRequired(array $data, array $requiredFields): array
    {
        $errors = [];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[$field] = "O campo {$field} é obrigatório";
            }
        }

        return $errors;
    }

    protected function getPaginationParams(): array
    {
        $page = (int) ($this->request->getGet('page') ?? 1);
        $limit = (int) ($this->request->getGet('limit') ?? 50);

        $page = max(1, $page);
        $limit = min(max(1, $limit), 100);

        $offset = ($page - 1) * $limit;

        return [
            'page' => $page,
            'limit' => $limit,
            'offset' => $offset
        ];
    }

    protected function respondWithPagination(
        array $data,
        int $total,
        int $page,
        int $limit
    ): ResponseInterface {
        $totalPages = ceil($total / $limit);

        return $this->respondSuccess([
            'items' => $data,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1
            ]
        ]);
    }
}
