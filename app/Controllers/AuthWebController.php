<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use CodeIgniter\HTTP\ResponseInterface;

class AuthWebController extends BaseController
{
    public function loginPage()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to(base_url('admin/dashboard'));
        }

        return view('auth/login');
    }

    public function login()
    {
        $validation = \Config\Services::validation();

        $validation->setRules([
            'email' => 'required|valid_email',
            'password' => 'required|min_length[6]'
        ], [
            'email' => [
                'required' => 'O e-mail é obrigatório',
                'valid_email' => 'Por favor, informe um e-mail válido'
            ],
            'password' => [
                'required' => 'A senha é obrigatória',
                'min_length' => 'A senha deve ter no mínimo 6 caracteres'
            ]
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()
                ->withInput()
                ->with('erro', implode('<br>', $validation->getErrors()));
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $remember = $this->request->getPost('remember');

        $usuarioModel = new UsuarioModel();
        $usuario = $usuarioModel
            ->where('email', $email)
            ->where('ativo', true)
            ->first();

        if (!$usuario) {
            return redirect()->back()
                ->withInput()
                ->with('erro', 'E-mail ou senha incorretos');
        }

        if (!password_verify($password, $usuario['password_hash'])) {
            return redirect()->back()
                ->withInput()
                ->with('erro', 'E-mail ou senha incorretos');
        }

        $usuarioModel->update($usuario['id'], [
            'ultimo_login' => date('Y-m-d H:i:s')
        ]);

        $sessionData = [
            'usuario_id' => $usuario['id'],
            'usuario_nome' => $usuario['nome'],
            'usuario_email' => $usuario['email'],
            'usuario_tipo' => $usuario['tipo_usuario'],
            'empresa_id' => $usuario['empresa_id'],
            'isLoggedIn' => true
        ];

        session()->set($sessionData);

        if ($remember) {
            session()->markAsFlashdata('isLoggedIn', 7200);
        }

        return redirect()->to(base_url('admin/dashboard'))
            ->with('sucesso', 'Login realizado com sucesso! Bem-vindo, ' . $usuario['nome']);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('login'))
            ->with('sucesso', 'Logout realizado com sucesso!');
    }

    public function forgotPassword()
    {
        return view('auth/forgot-password');
    }
}
