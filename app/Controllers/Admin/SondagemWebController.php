<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SondagemModel;
use App\Models\ObraModel;
use App\Models\ProjetoModel;
use App\Repositories\SondagemRepository;

class SondagemWebController extends BaseController
{
    private SondagemModel $model;
    private SondagemRepository $repository;

    public function __construct()
    {
        $this->model = new SondagemModel();
        $this->repository = new SondagemRepository();
    }

    public function index()
    {
        $status = $this->request->getGet('status');

        $query = $this->model
            ->select('sondagens.*, obras.nome as obra_nome, projetos.nome as projeto_nome')
            ->join('obras', 'obras.id = sondagens.obra_id', 'left')
            ->join('projetos', 'projetos.id = obras.projeto_id', 'left');

        if ($status) {
            $query->where('sondagens.status', $status);
        }

        $sondagens = $query->orderBy('sondagens.created_at', 'DESC')->findAll();

        $data = [
            'title' => 'Sondagens',
            'sondagens' => $sondagens,
            'breadcrumb' => [
                ['title' => 'Sondagens', 'url' => '', 'active' => true]
            ]
        ];

        return view('sondagens/index', $data);
    }

    public function create()
    {
        $obraModel = new ObraModel();
        $obras = $obraModel->where('ativo', true)->findAll();

        $data = [
            'title' => 'Nova Sondagem',
            'obras' => $obras,
            'breadcrumb' => [
                ['title' => 'Sondagens', 'url' => base_url('admin/sondagens'), 'active' => false],
                ['title' => 'Nova', 'url' => '', 'active' => true]
            ]
        ];

        return view('sondagens/create', $data);
    }

    public function show($id = null)
    {
        $sondagem = $this->repository->findWithRelations($id);

        if (!$sondagem) {
            return redirect()->to(base_url('admin/sondagens'))
                ->with('erro', 'Sondagem não encontrada');
        }

        $data = [
            'title' => 'Sondagem ' . $sondagem['codigo_sondagem'],
            'sondagem' => $sondagem,
            'breadcrumb' => [
                ['title' => 'Sondagens', 'url' => base_url('admin/sondagens'), 'active' => false],
                ['title' => $sondagem['codigo_sondagem'], 'url' => '', 'active' => true]
            ]
        ];

        return view('sondagens/show', $data);
    }

    public function edit($id = null)
    {
        $sondagem = $this->repository->find($id);

        if (!$sondagem) {
            return redirect()->to(base_url('admin/sondagens'))
                ->with('erro', 'Sondagem não encontrada');
        }

        $obraModel = new ObraModel();
        $obras = $obraModel->where('ativo', true)->findAll();

        $data = [
            'title' => 'Editar Sondagem',
            'sondagem' => $sondagem,
            'obras' => $obras,
            'breadcrumb' => [
                ['title' => 'Sondagens', 'url' => base_url('admin/sondagens'), 'active' => false],
                ['title' => 'Editar', 'url' => '', 'active' => true]
            ]
        ];

        return view('sondagens/edit', $data);
    }
}
