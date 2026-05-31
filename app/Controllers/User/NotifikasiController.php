<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\NotifikasiModel;

class NotifikasiController extends BaseController
{
    protected NotifikasiModel $notifModel;

    public function __construct()
    {
        $this->notifModel = new NotifikasiModel();
    }

    /**
     * AJAX: Ambil notifikasi terbaru + count unread.
     */
    public function get()
    {
        $userId = (int) session()->get('user_id');

        $notifikasi = $this->notifModel->getForUser($userId, 15);
        $unread     = $this->notifModel->countUnread($userId);

        return $this->response->setJSON([
            'unread'     => $unread,
            'notifikasi' => $notifikasi,
        ]);
    }

    /**
     * AJAX: Tandai semua sebagai dibaca.
     */
    public function markAllRead()
    {
        $userId = (int) session()->get('user_id');
        $this->notifModel->markAllRead($userId);

        return $this->response->setJSON(['status' => true]);
    }

    /**
     * AJAX: Tandai satu & redirect ke URL.
     */
    public function read(int $id)
    {
        $userId = (int) session()->get('user_id');
        $notif  = $this->notifModel->where('id', $id)->where('user_id', $userId)->first();

        if ($notif) {
            $this->notifModel->markRead($id, $userId);
            if (! empty($notif['url'])) {
                return redirect()->to(base_url($notif['url']));
            }
        }

        return redirect()->back();
    }
}
