<?php

namespace App\Controllers\SuperAdmin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * BackupController
 *
 * Mengelola backup dan restore database untuk Super Admin.
 * Mendukung mysqldump (jika tersedia) atau fallback PHP murni.
 */
class BackupController extends BaseController
{
    /** Direktori penyimpanan file backup */
    private string $backupDir;

    public function __construct()
    {
        $this->backupDir = WRITEPATH . 'backups' . DIRECTORY_SEPARATOR;

        // Buat direktori backup jika belum ada
        if (! is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
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

    /**
     * Ambil daftar file backup yang sudah ada.
     */
    private function getBackupFiles(): array
    {
        $files = glob($this->backupDir . '*.sql') ?: [];
        usort($files, fn($a, $b) => filemtime($b) - filemtime($a));

        return array_map(function (string $path) {
            return [
                'name'     => basename($path),
                'path'     => $path,
                'size'     => $this->formatBytes(filesize($path)),
                'modified' => date('d/m/Y H:i:s', filemtime($path)),
            ];
        }, $files);
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }

    // -------------------------------------------------------------------------
    // index — halaman backup & restore
    // -------------------------------------------------------------------------

    public function index(): string
    {
        return view('superadmin/backup/index', [
            'backupFiles' => $this->getBackupFiles(),
            'menus'       => $this->getMenus(),
        ]);
    }

    // -------------------------------------------------------------------------
    // backup — buat dump SQL dan kirim sebagai download
    // -------------------------------------------------------------------------

    public function backup(): ResponseInterface
    {
        $dbConfig = \Config\Database::connect()->getConnection();
        $hostname = $dbConfig->hostname ?? 'localhost';
        $database = $dbConfig->database;
        $username = $dbConfig->username;
        $password = $dbConfig->password;
        $port     = $dbConfig->port ?? 3306;

        $filename   = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $outputPath = $this->backupDir . $filename;

        // Coba mysqldump terlebih dahulu
        $mysqldump = $this->findMysqldump();

        if ($mysqldump !== null) {
            $sql = $this->dumpViaMysqldump($mysqldump, $hostname, (int) $port, $database, $username, $password, $outputPath);
        } else {
            $sql = $this->dumpViaPHP($database, $outputPath);
        }

        if (! $sql) {
            return redirect()->to(base_url('superadmin/backup'))
                ->with('error', 'Gagal membuat backup database.');
        }

        // Kirim file sebagai download
        return $this->response
            ->setHeader('Content-Type', 'application/octet-stream')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Content-Length', (string) filesize($outputPath))
            ->setBody(file_get_contents($outputPath));
    }

    /**
     * Cari path mysqldump yang tersedia di sistem.
     */
    private function findMysqldump(): ?string
    {
        $candidates = [
            'mysqldump',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/opt/homebrew/bin/mysqldump',
        ];

        foreach ($candidates as $cmd) {
            $output = [];
            $code   = 0;
            exec(escapeshellcmd($cmd) . ' --version 2>&1', $output, $code);
            if ($code === 0) {
                return $cmd;
            }
        }

        return null;
    }

    /**
     * Buat dump menggunakan mysqldump CLI.
     */
    private function dumpViaMysqldump(
        string $mysqldump,
        string $host,
        int    $port,
        string $database,
        string $username,
        string $password,
        string $outputPath
    ): bool {
        $passArg = $password !== '' ? '-p' . escapeshellarg($password) : '';

        $cmd = sprintf(
            '%s -h %s -P %d -u %s %s --single-transaction --routines --triggers %s > %s 2>&1',
            escapeshellcmd($mysqldump),
            escapeshellarg($host),
            $port,
            escapeshellarg($username),
            $passArg,
            escapeshellarg($database),
            escapeshellarg($outputPath)
        );

        exec($cmd, $output, $returnCode);

        return $returnCode === 0 && file_exists($outputPath) && filesize($outputPath) > 0;
    }

    /**
     * Fallback: buat dump menggunakan PHP murni (tanpa mysqldump).
     */
    private function dumpViaPHP(string $database, string $outputPath): bool
    {
        $db = \Config\Database::connect();

        $sql  = "-- SiapASN Simulation Center — Database Backup\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Database: {$database}\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        // Ambil semua tabel
        $tables = $db->query('SHOW TABLES')->getResultArray();

        foreach ($tables as $tableRow) {
            $table = reset($tableRow);

            // CREATE TABLE statement
            $createRow = $db->query("SHOW CREATE TABLE `{$table}`")->getRowArray();
            $createSql = $createRow['Create Table'] ?? '';

            $sql .= "-- --------------------------------------------------------\n";
            $sql .= "-- Table: `{$table}`\n";
            $sql .= "-- --------------------------------------------------------\n\n";
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $createSql . ";\n\n";

            // Data rows
            $rows = $db->query("SELECT * FROM `{$table}`")->getResultArray();

            if (! empty($rows)) {
                $columns = '`' . implode('`, `', array_keys($rows[0])) . '`';
                $sql .= "INSERT INTO `{$table}` ({$columns}) VALUES\n";

                $valueLines = [];
                foreach ($rows as $row) {
                    $values = array_map(function ($val) use ($db) {
                        if ($val === null) {
                            return 'NULL';
                        }
                        return "'" . $db->escapeString((string) $val) . "'";
                    }, array_values($row));

                    $valueLines[] = '(' . implode(', ', $values) . ')';
                }

                $sql .= implode(",\n", $valueLines) . ";\n\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $written = file_put_contents($outputPath, $sql);

        return $written !== false && $written > 0;
    }

    // -------------------------------------------------------------------------
    // restore — upload dan eksekusi file SQL
    // -------------------------------------------------------------------------

    public function restore(): RedirectResponse
    {
        $file = $this->request->getFile('sql_file');

        if (! $file || ! $file->isValid()) {
            return redirect()->to(base_url('superadmin/backup'))
                ->with('error', 'File SQL tidak valid atau tidak diunggah.');
        }

        if ($file->getClientExtension() !== 'sql') {
            return redirect()->to(base_url('superadmin/backup'))
                ->with('error', 'Hanya file .sql yang diperbolehkan.');
        }

        // Simpan file sementara
        $tmpPath = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . $file->getRandomName();
        $file->move(WRITEPATH . 'uploads', basename($tmpPath));

        $sqlContent = file_get_contents($tmpPath);
        @unlink($tmpPath);

        if (empty($sqlContent)) {
            return redirect()->to(base_url('superadmin/backup'))
                ->with('error', 'File SQL kosong atau tidak dapat dibaca.');
        }

        // Eksekusi SQL statement per statement
        $db = \Config\Database::connect();

        try {
            $db->query('SET FOREIGN_KEY_CHECKS=0');

            // Pisahkan statement berdasarkan titik koma di akhir baris
            $statements = $this->splitSqlStatements($sqlContent);

            foreach ($statements as $stmt) {
                $stmt = trim($stmt);
                if ($stmt === '' || str_starts_with($stmt, '--')) {
                    continue;
                }
                $db->query($stmt);
            }

            $db->query('SET FOREIGN_KEY_CHECKS=1');
        } catch (\Throwable $e) {
            $db->query('SET FOREIGN_KEY_CHECKS=1');
            return redirect()->to(base_url('superadmin/backup'))
                ->with('error', 'Restore gagal: ' . $e->getMessage());
        }

        return redirect()->to(base_url('superadmin/backup'))
            ->with('success', 'Database berhasil di-restore dari file SQL.');
    }

    /**
     * Pisahkan konten SQL menjadi array statement individual.
     * Menangani string literal dan komentar dengan benar.
     */
    private function splitSqlStatements(string $sql): array
    {
        $statements = [];
        $current    = '';
        $inString   = false;
        $stringChar = '';
        $len        = strlen($sql);

        for ($i = 0; $i < $len; $i++) {
            $char = $sql[$i];

            if ($inString) {
                $current .= $char;
                if ($char === $stringChar && ($i === 0 || $sql[$i - 1] !== '\\')) {
                    $inString = false;
                }
            } elseif ($char === '"' || $char === "'") {
                $inString   = true;
                $stringChar = $char;
                $current   .= $char;
            } elseif ($char === ';') {
                $current .= $char;
                $stmt = trim($current);
                if ($stmt !== '') {
                    $statements[] = $stmt;
                }
                $current = '';
            } else {
                $current .= $char;
            }
        }

        $stmt = trim($current);
        if ($stmt !== '') {
            $statements[] = $stmt;
        }

        return $statements;
    }
}
