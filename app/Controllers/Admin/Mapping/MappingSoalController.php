<?php

namespace App\Controllers\Admin\Mapping;

use App\Controllers\BaseController;
use App\Models\KategoriModel;
use App\Models\MappingSoalModel;
use App\Models\SoalModel;
use App\Models\TryoutModel;

class MappingSoalController extends BaseController
{
    protected MappingSoalModel $mappingModel;
    protected TryoutModel      $tryoutModel;
    protected SoalModel        $soalModel;
    protected KategoriModel    $kategoriModel;

    public function __construct()
    {
        $this->mappingModel  = new MappingSoalModel();
        $this->tryoutModel   = new TryoutModel();
        $this->soalModel     = new SoalModel();
        $this->kategoriModel = new KategoriModel();
    }

    private function getMenus(): array
    {
        $db = \Config\Database::connect();
        return $db->table('menu_mapping')
            ->where('role', session()->get('role'))
            ->where('is_visible', 1)
            ->orderBy('urutan', 'ASC')
            ->get()->getResultArray();
    }

    /**
     * Tampilkan halaman mapping soal.
     * Jika tryoutId = 0, tampilkan pemilihan tryout.
     */
    public function index(int $tryoutId = 0)
    {
        $tryouts = $this->tryoutModel->findAll();

        $mappedSoals    = [];
        $availableSoals = [];
        $selectedTryout = null;
        $totalSoal      = 0;
        $kategoris      = $this->kategoriModel->getParents();

        if ($tryoutId > 0) {
            $selectedTryout = $this->tryoutModel->find($tryoutId);

            if (! $selectedTryout) {
                return redirect()->to(base_url('admin/mapping/soal'))
                    ->with('error', 'Tryout tidak ditemukan.');
            }

            $mappedSoals = $this->mappingModel->getByTryout($tryoutId);
            $totalSoal   = $this->mappingModel->getTotalByTryout($tryoutId);

            $mappedSoalIds  = array_column($mappedSoals, 'soal_id');
            $kategoriFilter    = $this->request->getGet('kategori_id') ?? '';
            $subKategoriFilter = $this->request->getGet('sub_kategori_id') ?? '';

            $db      = \Config\Database::connect();
            $builder = $db->table('soal s')
                ->select('s.id, s.pertanyaan, s.kategori_id, s.sub_kategori_id, k.nama AS nama_kategori, sk.nama AS nama_sub_kategori')
                ->join('kategori k', 'k.id = s.kategori_id', 'left')
                ->join('kategori sk', 'sk.id = s.sub_kategori_id', 'left')
                ->orderBy('s.id', 'ASC');

            if (! empty($mappedSoalIds)) {
                $builder->whereNotIn('s.id', $mappedSoalIds);
            }

            if ($kategoriFilter !== '') {
                $builder->where('s.kategori_id', (int) $kategoriFilter);
            }

            if ($subKategoriFilter !== '') {
                $builder->where('s.sub_kategori_id', (int) $subKategoriFilter);
            }

            $availableSoals = $builder->get()->getResultArray();

            // Sub-kategori untuk dropdown (jika kategori sudah dipilih)
            $subKategoris = $kategoriFilter !== ''
                ? $this->kategoriModel->getChildren((int) $kategoriFilter)
                : [];
        } else {
            $subKategoris      = [];
            $kategoriFilter    = '';
            $subKategoriFilter = '';
        }

        return view('admin/mapping/soal/index', [
            'tryouts'           => $tryouts,
            'selectedTryout'    => $selectedTryout,
            'tryoutId'          => $tryoutId,
            'mappedSoals'       => $mappedSoals,
            'availableSoals'    => $availableSoals,
            'totalSoal'         => $totalSoal,
            'kategoris'         => $kategoris,
            'subKategoris'      => $subKategoris,
            'kategoriFilter'    => $kategoriFilter,
            'subKategoriFilter' => $subKategoriFilter,
            'menus'             => $this->getMenus(),
        ]);
    }

    /**
     * POST AJAX: Tambah soal ke tryout.
     * Validasi tryout_id dan soal_id, cek duplikasi.
     */
    public function store()
    {
        $tryoutId = (int) $this->request->getPost('tryout_id');
        $soalId   = (int) $this->request->getPost('soal_id');

        if ($tryoutId <= 0 || $soalId <= 0) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'tryout_id dan soal_id wajib diisi.',
            ]);
        }

        // Cek duplikasi
        if ($this->mappingModel->isDuplicate($tryoutId, $soalId)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Soal sudah terdapat dalam tryout ini',
            ]);
        }

        // Tentukan urutan berikutnya (max urutan + 1)
        $db      = \Config\Database::connect();
        $maxRow  = $db->table('mapping_soal')
            ->selectMax('urutan')
            ->where('tryout_id', $tryoutId)
            ->get()
            ->getRowArray();

        $nextUrutan = ($maxRow && $maxRow['urutan'] !== null) ? (int) $maxRow['urutan'] + 1 : 1;

        $this->mappingModel->insert([
            'tryout_id'  => $tryoutId,
            'soal_id'    => $soalId,
            'urutan'     => $nextUrutan,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['status' => true]);
    }

    /**
     * POST: Hapus mapping soal dari tryout.
     */
    public function delete(int $id)
    {
        $mapping = $this->mappingModel->find($id);

        if (! $mapping) {
            return redirect()->back()->with('error', 'Mapping tidak ditemukan.');
        }

        $tryoutId = (int) $mapping['tryout_id'];
        $this->mappingModel->delete($id);

        return redirect()->to(base_url("admin/mapping/soal/{$tryoutId}"))
            ->with('success', 'Soal berhasil dihapus dari tryout.');
    }

    /**
     * POST AJAX: Perbarui urutan untuk beberapa mapping sekaligus.
     * Menerima array of {id, urutan}.
     */
    public function updateUrutan()
    {
        $items = $this->request->getPost('items');

        if (! is_array($items)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Data urutan tidak valid.',
            ]);
        }

        foreach ($items as $item) {
            $id     = (int) ($item['id'] ?? 0);
            $urutan = (int) ($item['urutan'] ?? 0);

            if ($id > 0) {
                $this->mappingModel->updateUrutan($id, $urutan);
            }
        }

        return $this->response->setJSON(['status' => true]);
    }
}
