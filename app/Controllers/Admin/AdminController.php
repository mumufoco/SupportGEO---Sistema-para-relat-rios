<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SondagemModel;
use App\Models\ObraModel;

class AdminController extends BaseController
{
    public function index()
    {
        return redirect()->to(base_url('admin/dashboard'));
    }

    public function dashboard()
    {
        $sondagemModel = new SondagemModel();
        $obraModel = new ObraModel();

        $stats = [
            'total_sondagens' => $sondagemModel->countAll(),
            'aprovadas' => $sondagemModel->where('status', 'aprovado')->countAllResults(),
            'pendentes' => $sondagemModel->whereIn('status', ['rascunho', 'em_analise'])->countAllResults(),
            'obras_ativas' => $obraModel->where('ativo', true)->countAllResults(),
            'conformidade_media' => 95
        ];

        $ultimas_sondagens = $sondagemModel
            ->select('sondagens.*, obras.nome as obra_nome, projetos.nome as projeto_nome')
            ->join('obras', 'obras.id = sondagens.obra_id', 'left')
            ->join('projetos', 'projetos.id = obras.projeto_id', 'left')
            ->orderBy('sondagens.created_at', 'DESC')
            ->limit(10)
            ->findAll();

        $data = [
            'title' => 'Dashboard',
            'stats' => $stats,
            'ultimas_sondagens' => $ultimas_sondagens
        ];

        return view('admin/dashboard', $data);
    }
}
