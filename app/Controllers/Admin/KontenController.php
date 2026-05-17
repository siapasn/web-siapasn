<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\WebContentModel;

class KontenController extends BaseController
{
    protected WebContentModel $contentModel;

    public function __construct()
    {
        $this->contentModel = new WebContentModel();
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

    // -------------------------------------------------------------------------
    // index() — Daftar semua konten web
    // -------------------------------------------------------------------------

    public function index()
    {
        $kontenList = $this->contentModel->orderBy('tipe', 'ASC')->orderBy('judul', 'ASC')->findAll();

        return view('admin/konten/index', [
            'kontenList' => $kontenList,
            'menus'      => $this->getMenus(),
        ]);
    }

    // -------------------------------------------------------------------------
    // create() — Form tambah konten baru
    // -------------------------------------------------------------------------

    public function create()
    {
        return view('admin/konten/form', [
            'konten' => null,
            'menus'  => $this->getMenus(),
        ]);
    }

    // -------------------------------------------------------------------------
    // store() — POST: simpan konten baru
    // -------------------------------------------------------------------------

    public function store()
    {
        $rules = [
            'slug'  => 'required|max_length[100]|is_unique[web_content.slug]',
            'judul' => 'required|max_length[200]',
            'tipe'  => 'required|in_list[halaman,teks,angka]',
        ];

        $messages = [
            'slug' => [
                'required'  => 'Slug tidak boleh kosong.',
                'is_unique' => 'Slug sudah digunakan.',
            ],
            'judul' => ['required' => 'Judul tidak boleh kosong.'],
            'tipe'  => ['required' => 'Tipe harus dipilih.'],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->contentModel->insert([
            'slug'      => trim($this->request->getPost('slug')),
            'judul'     => trim($this->request->getPost('judul')),
            'konten'    => $this->request->getPost('konten') ?? '',
            'tipe'      => $this->request->getPost('tipe'),
            'is_active' => (int) $this->request->getPost('is_active'),
        ]);

        return redirect()->to(base_url('admin/konten'))
            ->with('success', 'Konten berhasil ditambahkan.');
    }

    // -------------------------------------------------------------------------
    // edit(int $id) — Form edit konten
    // -------------------------------------------------------------------------

    public function edit(int $id)
    {
        $konten = $this->contentModel->find($id);
        if (! $konten) {
            return redirect()->to(base_url('admin/konten'))
                ->with('error', 'Konten tidak ditemukan.');
        }

        return view('admin/konten/form', [
            'konten' => $konten,
            'menus'  => $this->getMenus(),
        ]);
    }

    // -------------------------------------------------------------------------
    // update(int $id) — POST: simpan perubahan konten
    // -------------------------------------------------------------------------

    public function update(int $id)
    {
        $konten = $this->contentModel->find($id);
        if (! $konten) {
            return redirect()->to(base_url('admin/konten'))
                ->with('error', 'Konten tidak ditemukan.');
        }

        $rules = [
            'slug'  => "required|max_length[100]|is_unique[web_content.slug,id,{$id}]",
            'judul' => 'required|max_length[200]',
            'tipe'  => 'required|in_list[halaman,teks,angka]',
        ];

        $messages = [
            'slug' => [
                'required'  => 'Slug tidak boleh kosong.',
                'is_unique' => 'Slug sudah digunakan oleh konten lain.',
            ],
            'judul' => ['required' => 'Judul tidak boleh kosong.'],
            'tipe'  => ['required' => 'Tipe harus dipilih.'],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->contentModel->update($id, [
            'slug'      => trim($this->request->getPost('slug')),
            'judul'     => trim($this->request->getPost('judul')),
            'konten'    => $this->request->getPost('konten') ?? '',
            'tipe'      => $this->request->getPost('tipe'),
            'is_active' => (int) $this->request->getPost('is_active'),
        ]);

        return redirect()->to(base_url('admin/konten'))
            ->with('success', 'Konten berhasil diperbarui.');
    }

    // -------------------------------------------------------------------------
    // delete(int $id) — POST: hapus konten
    // -------------------------------------------------------------------------

    public function delete(int $id)
    {
        $konten = $this->contentModel->find($id);
        if (! $konten) {
            return redirect()->to(base_url('admin/konten'))
                ->with('error', 'Konten tidak ditemukan.');
        }

        // Lindungi slug sistem dari penghapusan
        $protected = [
            'syarat-ketentuan', 'kebijakan-privasi', 'hubungi-kami',
            'kontak_email', 'kontak_whatsapp', 'kontak_alamat',
            'hero_tagline', 'hero_deskripsi',
            'stat_pengguna', 'stat_soal', 'stat_paket',
        ];

        if (in_array($konten['slug'], $protected, true)) {
            return redirect()->to(base_url('admin/konten'))
                ->with('error', 'Konten sistem tidak dapat dihapus. Anda hanya bisa menonaktifkannya.');
        }

        $this->contentModel->delete($id);

        return redirect()->to(base_url('admin/konten'))
            ->with('success', 'Konten berhasil dihapus.');
    }
}
