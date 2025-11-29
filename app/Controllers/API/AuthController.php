<?php

namespace App\Controllers\API;

use App\Models\UsuarioModel;

class AuthController extends BaseAPIController
{
    private UsuarioModel $model;

    public function __construct()
    {
        $this->model = new UsuarioModel();
    }

    public function login()
    {
        try {
            $data = $this->request->getJSON(true);

            $errors = $this->validateRequired($data, ['email', 'password']);
            if (!empty($errors)) {
                return $this->respondValidationError($errors);
            }

            $user = $this->model->authenticate($data['email'], $data['password']);

            if (!$user) {
                return $this->respondUnauthorized('Email ou senha inválidos');
            }

            $token = $this->generateToken($user);

            unset($user['password_hash']);

            return $this->respondSuccess([
                'user' => $user,
                'token' => $token
            ], 'Login realizado com sucesso');
        } catch (\Exception $e) {
            return $this->respondError('Erro ao realizar login: ' . $e->getMessage(), 500);
        }
    }

    public function register()
    {
        try {
            $data = $this->request->getJSON(true);

            $errors = $this->validateRequired($data, [
                'empresa_id',
                'nome',
                'email',
                'password'
            ]);

            if (!empty($errors)) {
                return $this->respondValidationError($errors);
            }

            $existingUser = $this->model->findByEmail($data['email']);
            if ($existingUser) {
                return $this->respondError('Email já cadastrado', 400);
            }

            $user = $this->model->createUser($data);

            unset($user[0]['password_hash']);

            return $this->respondCreated($user[0], 'Usuário cadastrado com sucesso');
        } catch (\Exception $e) {
            return $this->respondError('Erro ao cadastrar usuário: ' . $e->getMessage(), 500);
        }
    }

    public function me()
    {
        try {
            $usuarioId = $this->requireAuth();

            $user = $this->model->find($usuarioId);
            if (!$user) {
                return $this->respondNotFound('Usuário não encontrado');
            }

            unset($user['password_hash']);

            return $this->respondSuccess($user);
        } catch (\Exception $e) {
            return $this->respondError('Erro ao buscar usuário: ' . $e->getMessage(), 500);
        }
    }

    public function updatePassword()
    {
        try {
            $usuarioId = $this->requireAuth();
            $data = $this->request->getJSON(true);

            $errors = $this->validateRequired($data, ['current_password', 'new_password']);
            if (!empty($errors)) {
                return $this->respondValidationError($errors);
            }

            $user = $this->model->find($usuarioId);
            if (!$user) {
                return $this->respondNotFound('Usuário não encontrado');
            }

            if (!password_verify($data['current_password'], $user['password_hash'])) {
                return $this->respondError('Senha atual incorreta', 400);
            }

            $this->model->updatePassword($usuarioId, $data['new_password']);

            return $this->respondSuccess(null, 'Senha atualizada com sucesso');
        } catch (\Exception $e) {
            return $this->respondError('Erro ao atualizar senha: ' . $e->getMessage(), 500);
        }
    }

    private function generateToken(array $user): string
    {
        $payload = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'empresa_id' => $user['empresa_id'],
            'tipo_usuario' => $user['tipo_usuario'],
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24 * 7)
        ];

        return base64_encode(json_encode($payload));
    }
}
