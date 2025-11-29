<?php

namespace Config;

class Upload
{
    public int $maxSize = 10485760;
    public array $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'xlsx', 'xls', 'csv'];
    public string $uploadPath = WRITEPATH . 'uploads/';
    public int $maxWidth = 4096;
    public int $maxHeight = 4096;

    public array $uploadPaths = [
        'fotos' => WRITEPATH . 'uploads/fotos/',
        'imports' => WRITEPATH . 'uploads/imports/',
        'reports' => WRITEPATH . 'uploads/reports/',
        'assinaturas' => WRITEPATH . 'uploads/assinaturas/',
        'logos' => WRITEPATH . 'uploads/logos/',
    ];
}
