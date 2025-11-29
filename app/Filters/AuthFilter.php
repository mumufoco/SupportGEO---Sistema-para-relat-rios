<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (!$session->get('isLoggedIn')) {
            $session->setFlashdata('erro', 'Você precisa fazer login para acessar esta página.');
            return redirect()->to(base_url('login'));
        }

        if (!$session->get('usuario_id')) {
            $session->destroy();
            $session->setFlashdata('erro', 'Sessão inválida. Faça login novamente.');
            return redirect()->to(base_url('login'));
        }

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
