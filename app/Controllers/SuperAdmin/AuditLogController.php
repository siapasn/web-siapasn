<?php

namespace App\Controllers\SuperAdmin;

use App\Controllers\BaseController;
use App\Models\AuditLogModel;

/**
 * AuditLogController
 *
 * Menampilkan log aktivitas sistem untuk Super Admin.
 * Mendukung filter berdasarkan user dan rentang tanggal.
 */
class AuditLogController extends BaseController
{
    protected AuditLogModel $auditLogModel;

    public function __construct()
    {
        $this->auditLogModel = new AuditLogModel();
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

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
    // index — daftar log audit
    // -------------------------------------------------------------------------

    public function index(): string
    {
        $db = \Config\Database::connect();

        $userId    = $this->request->getGet('user_id')    ?? '';
        $dateFrom  = $this->request->getGet('date_from')  ?? '';
        $dateTo    = $this->request->getGet('date_to')    ?? '';

        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 25;

        $builder = $db->table('audit_log al')
            ->select('al.*, u.nama AS user_nama, u.email AS user_email')
            ->join('users u', 'u.id = al.user_id', 'left')
            ->orderBy('al.created_at', 'DESC');

        if ($userId !== '') {
            $builder->where('al.user_id', (int) $userId);
        }

        if ($dateFrom !== '') {
            $builder->where('DATE(al.created_at) >=', $dateFrom);
        }

        if ($dateTo !== '') {
            $builder->where('DATE(al.created_at) <=', $dateTo);
        }

        $total = $builder->countAllResults(false);
        $logs  = $builder->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();

        // Daftar user untuk dropdown filter
        $users = $db->table('users')
            ->select('id, nama, email')
            ->orderBy('nama', 'ASC')
            ->get()->getResultArray();

        return view('superadmin/audit-log/index', [
            'logs'     => $logs,
            'users'    => $users,
            'userId'   => $userId,
            'dateFrom' => $dateFrom,
            'dateTo'   => $dateTo,
            'total'    => $total,
            'page'     => $page,
            'perPage'  => $perPage,
            'menus'    => $this->getMenus(),
        ]);
    }
}
