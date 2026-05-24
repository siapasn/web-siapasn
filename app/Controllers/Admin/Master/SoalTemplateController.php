<?php

namespace App\Controllers\Admin\Master;

use App\Controllers\BaseController;

class SoalTemplateController extends BaseController
{
    /**
     * Serve file template import soal dari public/uploads/soal/template.csv.
     * File dikelola manual oleh admin — tidak di-generate otomatis.
     */
    public function download()
    {
        $filePath = FCPATH . 'uploads/soal/template.csv';

        if (! is_file($filePath)) {
            return redirect()->back()
                ->with('error', 'File template tidak ditemukan. Hubungi administrator.');
        }

        $filename = 'template_import_soal.csv';

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->setHeader('Pragma', 'no-cache')
            ->setBody(file_get_contents($filePath));
    }
}
