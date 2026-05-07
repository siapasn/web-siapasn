<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #333;
            padding: 20px;
        }
        h2 {
            font-size: 16px;
            margin-bottom: 4px;
            color: #1e293b;
        }
        .subtitle {
            font-size: 11px;
            color: #64748b;
            margin-bottom: 16px;
        }
        .summary {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 4px;
            padding: 10px 14px;
            margin-bottom: 16px;
            display: inline-block;
        }
        .summary strong {
            font-size: 13px;
            color: #166534;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        thead tr {
            background-color: #1e293b;
            color: #fff;
        }
        thead th {
            padding: 7px 8px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
            white-space: nowrap;
        }
        tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
        tbody td {
            padding: 6px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10px;
            vertical-align: top;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-success  { background-color: #dcfce7; color: #166534; }
        .badge-pending  { background-color: #fef9c3; color: #854d0e; }
        .badge-failed   { background-color: #fee2e2; color: #991b1b; }
        .badge-expired  { background-color: #f1f5f9; color: #475569; }
        .footer {
            margin-top: 20px;
            font-size: 10px;
            color: #94a3b8;
            text-align: right;
        }
    </style>
</head>
<body>

    <h2>Laporan Transaksi</h2>
    <div class="subtitle">
        Periode: <?= esc($filters['tanggalDari']) ?> s/d <?= esc($filters['tanggalSampai']) ?>
        <?php if ($filters['status'] !== 'all' && $filters['status'] !== ''): ?>
            &nbsp;&bull;&nbsp; Status: <?= esc(strtoupper($filters['status'])) ?>
        <?php endif; ?>
    </div>

    <div class="summary">
        Total Pendapatan (Success):
        <strong>Rp <?= number_format($totalPendapatan, 0, ',', '.') ?></strong>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:30px">No</th>
                <th>Kode Transaksi</th>
                <th>Nama User</th>
                <th>Produk</th>
                <th>Tanggal</th>
                <th class="text-right">Harga Asli</th>
                <th class="text-right">Diskon</th>
                <th class="text-right">Harga Bayar</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (! empty($transaksis)): ?>
                <?php foreach ($transaksis as $i => $t): ?>
                    <tr>
                        <td class="text-center"><?= $i + 1 ?></td>
                        <td><?= esc($t['kode_transaksi']) ?></td>
                        <td><?= esc($t['user_nama']) ?></td>
                        <td><?= esc($t['produk_nama']) ?></td>
                        <td><?= esc(date('d/m/Y H:i', strtotime($t['created_at']))) ?></td>
                        <td class="text-right">Rp <?= number_format((float) $t['harga_asli'], 0, ',', '.') ?></td>
                        <td class="text-right">Rp <?= number_format((float) $t['diskon'], 0, ',', '.') ?></td>
                        <td class="text-right">Rp <?= number_format((float) $t['harga_bayar'], 0, ',', '.') ?></td>
                        <td class="text-center">
                            <?php
                            $badgeClass = match($t['status']) {
                                'success' => 'badge-success',
                                'pending' => 'badge-pending',
                                'failed'  => 'badge-failed',
                                default   => 'badge-expired',
                            };
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= esc($t['status']) ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px; color: #94a3b8;">
                        Tidak ada data transaksi untuk periode ini.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: <?= date('d/m/Y H:i:s') ?>
    </div>

</body>
</html>
