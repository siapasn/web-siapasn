<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TryoutEventModel;

class TryoutEventController extends BaseController
{
    protected TryoutEventModel $eventModel;

    public function __construct()
    {
        $this->eventModel = new TryoutEventModel();
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
     * Daftar semua event tryout.
     */
    public function index()
    {
        $events = $this->eventModel->getAllWithDetails();

        return view('admin/tryout-event/index', [
            'events' => $events,
            'menus'  => $this->getMenus(),
        ]);
    }

    /**
     * Form tambah event.
     */
    public function create()
    {
        $db = \Config\Database::connect();
        $tryouts = $db->table('tryout')
            ->where('is_active', 1)
            ->orderBy('nama', 'ASC')
            ->get()->getResultArray();

        return view('admin/tryout-event/form', [
            'event'   => null,
            'tryouts' => $tryouts,
            'action'  => base_url('admin/tryout-event/store'),
            'menus'   => $this->getMenus(),
        ]);
    }

    /**
     * Simpan event baru.
     */
    public function store()
    {
        $rules = [
            'nama'               => 'required|min_length[3]|max_length[255]',
            'tryout_id'          => 'required|is_natural_no_zero',
            'mulai_pendaftaran'  => 'required|valid_date[Y-m-d\TH:i]',
            'tutup_pendaftaran'  => 'required|valid_date[Y-m-d\TH:i]',
            'mulai_pelaksanaan'  => 'required|valid_date[Y-m-d\TH:i]',
            'tutup_pelaksanaan'  => 'required|valid_date[Y-m-d\TH:i]',
            'max_percobaan'      => 'required|integer|greater_than[0]|less_than[10]',
            'banner'             => 'if_exist|is_image[banner]|max_size[banner,2048]|ext_in[banner,jpg,jpeg,png,webp]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $bannerUrl = null;
        $file = $this->request->getFile('banner');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $bannerName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/event', $bannerName);
            $bannerUrl = 'uploads/event/' . $bannerName;
        }

        helper('slug');
        $eventId = $this->eventModel->insert([
            'nama'               => $this->request->getPost('nama'),
            'slug'               => make_unique_slug('tryout_event', $this->request->getPost('nama')),
            'tryout_id'          => (int) $this->request->getPost('tryout_id'),
            'deskripsi'          => $this->request->getPost('deskripsi') ?: null,
            'banner_url'         => $bannerUrl,
            'mulai_pendaftaran'  => date('Y-m-d H:i:s', strtotime($this->request->getPost('mulai_pendaftaran'))),
            'tutup_pendaftaran'  => date('Y-m-d H:i:s', strtotime($this->request->getPost('tutup_pendaftaran'))),
            'mulai_pelaksanaan'  => date('Y-m-d H:i:s', strtotime($this->request->getPost('mulai_pelaksanaan'))),
            'tutup_pelaksanaan'  => date('Y-m-d H:i:s', strtotime($this->request->getPost('tutup_pelaksanaan'))),
            'max_percobaan'      => (int) $this->request->getPost('max_percobaan'),
            'is_active'          => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        // Notifikasi ke semua user: event baru
        $namaEvent = $this->request->getPost('nama');
        $db2 = \Config\Database::connect();
        $allUsers = $db2->table('users')
            ->select('id')
            ->where('role', 'user')
            ->where('is_active', 1)
            ->get()->getResultArray();

        $now2 = date('Y-m-d H:i:s');
        $batch = [];
        foreach ($allUsers as $u) {
            $batch[] = [
                'user_id'    => $u['id'],
                'tipe'       => 'event',
                'judul'      => 'Event Tryout Baru!',
                'pesan'      => $namaEvent . ' — Daftar sekarang, gratis!',
                'url'        => 'user/tryout-event',
                'is_read'    => 0,
                'created_at' => $now2,
            ];
        }
        if (! empty($batch)) {
            $db2->table('notifikasi')->insertBatch($batch);
        }

        return redirect()->to(base_url('admin/tryout-event'))->with('success', 'Event tryout berhasil dibuat.');
    }

    /**
     * Form edit event.
     */
    public function edit(int $id)
    {
        $event = $this->eventModel->find($id);
        if (! $event) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Event tidak ditemukan.');
        }

        $db = \Config\Database::connect();
        $tryouts = $db->table('tryout')
            ->where('is_active', 1)
            ->orderBy('nama', 'ASC')
            ->get()->getResultArray();

        return view('admin/tryout-event/form', [
            'event'   => $event,
            'tryouts' => $tryouts,
            'action'  => base_url("admin/tryout-event/{$id}/update"),
            'menus'   => $this->getMenus(),
        ]);
    }

    /**
     * Update event.
     */
    public function update(int $id)
    {
        $event = $this->eventModel->find($id);
        if (! $event) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Event tidak ditemukan.');
        }

        $rules = [
            'nama'               => 'required|min_length[3]|max_length[255]',
            'tryout_id'          => 'required|is_natural_no_zero',
            'mulai_pendaftaran'  => 'required|valid_date[Y-m-d\TH:i]',
            'tutup_pendaftaran'  => 'required|valid_date[Y-m-d\TH:i]',
            'mulai_pelaksanaan'  => 'required|valid_date[Y-m-d\TH:i]',
            'tutup_pelaksanaan'  => 'required|valid_date[Y-m-d\TH:i]',
            'max_percobaan'      => 'required|integer|greater_than[0]|less_than[10]',
            'banner'             => 'if_exist|is_image[banner]|max_size[banner,2048]|ext_in[banner,jpg,jpeg,png,webp]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $bannerUrl = $event['banner_url'];
        $file = $this->request->getFile('banner');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            if ($bannerUrl && file_exists(FCPATH . $bannerUrl)) {
                @unlink(FCPATH . $bannerUrl);
            }
            $bannerName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/event', $bannerName);
            $bannerUrl = 'uploads/event/' . $bannerName;
        }

        if ($this->request->getPost('hapus_banner') && $bannerUrl) {
            if (file_exists(FCPATH . $bannerUrl)) {
                @unlink(FCPATH . $bannerUrl);
            }
            $bannerUrl = null;
        }

        helper('slug');
        $this->eventModel->update($id, [
            'nama'               => $this->request->getPost('nama'),
            'slug'               => make_unique_slug('tryout_event', $this->request->getPost('nama'), $id),
            'tryout_id'          => (int) $this->request->getPost('tryout_id'),
            'deskripsi'          => $this->request->getPost('deskripsi') ?: null,
            'banner_url'         => $bannerUrl,
            'mulai_pendaftaran'  => date('Y-m-d H:i:s', strtotime($this->request->getPost('mulai_pendaftaran'))),
            'tutup_pendaftaran'  => date('Y-m-d H:i:s', strtotime($this->request->getPost('tutup_pendaftaran'))),
            'mulai_pelaksanaan'  => date('Y-m-d H:i:s', strtotime($this->request->getPost('mulai_pelaksanaan'))),
            'tutup_pelaksanaan'  => date('Y-m-d H:i:s', strtotime($this->request->getPost('tutup_pelaksanaan'))),
            'max_percobaan'      => (int) $this->request->getPost('max_percobaan'),
            'is_active'          => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return redirect()->to(base_url('admin/tryout-event'))->with('success', 'Event tryout berhasil diperbarui.');
    }

    /**
     * Hapus event. Dicegah jika sudah ada peserta.
     */
    public function delete(int $id)
    {
        $event = $this->eventModel->find($id);
        if (! $event) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Event tidak ditemukan.');
        }

        $db = \Config\Database::connect();
        $hasPeserta = $db->table('tryout_event_peserta')
            ->where('event_id', $id)
            ->countAllResults() > 0;

        if ($hasPeserta) {
            return redirect()->to(base_url('admin/tryout-event'))
                ->with('error', 'Event tidak dapat dihapus karena sudah memiliki peserta terdaftar.');
        }

        $this->eventModel->delete($id);

        return redirect()->to(base_url('admin/tryout-event'))->with('success', 'Event tryout berhasil dihapus.');
    }

    /**
     * Lihat peserta event.
     */
    public function peserta(int $id)
    {
        $event = $this->eventModel->find($id);
        if (! $event) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Event tidak ditemukan.');
        }

        $db = \Config\Database::connect();

        $peserta = $db->table('tryout_event_peserta tep')
            ->select('tep.*, u.nama, u.email,
                      ht.total_nilai, ht.skor_total, ht.jumlah_benar, ht.jumlah_salah,
                      ht.status_lulus, ht.detail_kategori, ht.detail_passing_grade')
            ->join('users u', 'u.id = tep.user_id')
            ->join('hasil_tryout ht', 'ht.sesi_tryout_id = tep.sesi_tryout_id', 'left')
            ->where('tep.event_id', $id)
            ->orderBy('ht.total_nilai', 'DESC')
            ->orderBy('ht.skor_total', 'DESC')
            ->orderBy('tep.registered_at', 'ASC')
            ->get()->getResultArray();

        return view('admin/tryout-event/peserta', [
            'event'   => $event,
            'peserta' => $peserta,
            'menus'   => $this->getMenus(),
        ]);
    }
}
