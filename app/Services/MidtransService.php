<?php

namespace App\Services;

class MidtransService
{
    protected string $serverKey;
    protected string $clientKey;
    protected bool   $isProduction;
    protected string $snapUrl;

    /**
     * Metode pembayaran yang diizinkan beserta konfigurasinya.
     * key  = payment_method (disimpan di DB)
     * Snap enabled_payments value sesuai dokumentasi Midtrans.
     */
    public const PAYMENT_METHODS = [
        'qris' => [
            'label'    => 'QRIS',
            'icon'     => 'qris',
            'desc'     => 'Bayar dengan QR Code — semua e-wallet & m-banking',
            'enabled'  => ['qris'],
            'color'    => '#e31e24',
        ],
        'gopay' => [
            'label'    => 'GoPay',
            'icon'     => 'gopay',
            'desc'     => 'Bayar dengan saldo GoPay',
            'enabled'  => ['gopay'],
            'color'    => '#00aed6',
        ],
        'shopeepay' => [
            'label'    => 'ShopeePay',
            'icon'     => 'shopeepay',
            'desc'     => 'Bayar dengan saldo ShopeePay',
            'enabled'  => ['shopeepay'],
            'color'    => '#ee4d2d',
        ],
        'dana' => [
            'label'    => 'DANA',
            'icon'     => 'dana',
            'desc'     => 'Bayar dengan saldo DANA',
            'enabled'  => ['dana'],
            'color'    => '#108ee9',
        ],
        'mandiri' => [
            'label'    => 'Bank Transfer Mandiri',
            'icon'     => 'mandiri',
            'desc'     => 'Transfer via ATM / m-banking Mandiri',
            'enabled'  => ['echannel'],
            'color'    => '#003d79',
        ],
        'bni' => [
            'label'    => 'Bank Transfer BNI',
            'icon'     => 'bni',
            'desc'     => 'Transfer via ATM / m-banking BNI',
            'enabled'  => ['bni_va'],
            'color'    => '#f68b1e',
        ],
        'bri' => [
            'label'    => 'Bank Transfer BRI',
            'icon'     => 'bri',
            'desc'     => 'Transfer via ATM / m-banking BRI',
            'enabled'  => ['bri_va'],
            'color'    => '#005baa',
        ],
        'permata' => [
            'label'    => 'Bank Transfer Permata',
            'icon'     => 'permata',
            'desc'     => 'Transfer via ATM / m-banking Permata',
            'enabled'  => ['permata_va'],
            'color'    => '#e31e24',
        ],
    ];

    public function __construct()
    {
        $db      = \Config\Database::connect();
        $configs = $db->table('master_aplikasi')
            ->whereIn('config_key', ['midtrans_server_key', 'midtrans_client_key', 'midtrans_environment'])
            ->get()->getResultArray();

        $configMap = array_column($configs, 'config_value', 'config_key');

        $this->serverKey    = $configMap['midtrans_server_key'] ?? env('MIDTRANS_SERVER_KEY', '');
        $this->clientKey    = $configMap['midtrans_client_key'] ?? env('MIDTRANS_CLIENT_KEY', '');
        $this->isProduction = ($configMap['midtrans_environment'] ?? 'sandbox') === 'production';
        $this->snapUrl      = $this->isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    /**
     * Buat Snap token dengan metode pembayaran yang dibatasi.
     *
     * @param array  $transaksi   Data transaksi
     * @param array  $user        Data user
     * @param array  $produk      Data produk
     * @param string $paymentMethod Key dari PAYMENT_METHODS (kosong = semua metode)
     */
    public function createSnapToken(
        array  $transaksi,
        array  $user,
        array  $produk,
        string $paymentMethod = ''
    ): string {
        $payload = [
            'transaction_details' => [
                'order_id'     => $transaksi['kode_transaksi'],
                'gross_amount' => (int) $transaksi['harga_bayar'],
            ],
            'customer_details' => [
                'first_name' => $user['nama'],
                'email'      => $user['email'],
            ],
            'item_details' => [
                [
                    'id'       => (string) $produk['id'],
                    'price'    => (int) $transaksi['harga_bayar'],
                    'quantity' => 1,
                    'name'     => mb_substr($produk['nama'], 0, 50),
                ],
            ],
        ];

        // Batasi metode pembayaran jika dipilih
        if ($paymentMethod !== '' && isset(self::PAYMENT_METHODS[$paymentMethod])) {
            $payload['enabled_payments'] = self::PAYMENT_METHODS[$paymentMethod]['enabled'];
        } else {
            // Tampilkan semua metode yang didukung
            $payload['enabled_payments'] = array_merge(
                ...array_column(self::PAYMENT_METHODS, 'enabled')
            );
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $this->snapUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Basic ' . base64_encode($this->serverKey . ':'),
            ],
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 201) {
            throw new \RuntimeException('Midtrans API error (' . $httpCode . '): ' . $response);
        }

        $data = json_decode($response, true);
        return $data['token'] ?? '';
    }

    /**
     * Cek status transaksi langsung ke Midtrans Status API.
     * Digunakan sebagai fallback saat webhook tidak diterima (localhost/dev).
     */
    public function checkTransactionStatus(string $orderId): array
    {
        $baseUrl = $this->isProduction
            ? 'https://api.midtrans.com/v2/'
            : 'https://api.sandbox.midtrans.com/v2/';

        $url = $baseUrl . urlencode($orderId) . '/status';

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPGET        => true,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'Authorization: Basic ' . base64_encode($this->serverKey . ':'),
            ],
            CURLOPT_TIMEOUT        => 15,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \RuntimeException('Midtrans Status API error (' . $httpCode . '): ' . $response);
        }

        return json_decode($response, true) ?? [];
    }

    /**
     * Verifikasi signature webhook Midtrans.
     * Signature = SHA512(order_id + status_code + gross_amount + server_key)
     */
    public function verifyWebhookSignature(array $payload): bool
    {
        $orderId     = $payload['order_id']     ?? '';
        $statusCode  = $payload['status_code']  ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';

        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);
        return hash_equals($expected, $payload['signature_key'] ?? '');
    }

    /**
     * Deteksi payment_method & payment_channel dari payload webhook Midtrans.
     * Return ['method' => '...', 'channel' => '...']
     */
    public function detectPaymentInfo(array $payload): array
    {
        $type    = $payload['payment_type']  ?? '';
        $method  = '';
        $channel = '';

        switch ($type) {
            case 'qris':
                $method  = 'qris';
                $channel = $payload['acquirer'] ?? 'qris';
                break;
            case 'gopay':
                $method  = 'gopay';
                $channel = 'gopay';
                break;
            case 'shopeepay':
                $method  = 'shopeepay';
                $channel = 'shopeepay';
                break;
            case 'dana':
                $method  = 'dana';
                $channel = 'dana';
                break;
            case 'echannel': // Mandiri Bill
                $method  = 'mandiri';
                $channel = 'mandiri';
                break;
            case 'bank_transfer':
                $bank    = $payload['va_numbers'][0]['bank'] ?? ($payload['permata_va_number'] ? 'permata' : '');
                $method  = $bank ?: 'bank_transfer';
                $channel = $bank;
                break;
            case 'bni_va':
                $method  = 'bni';
                $channel = 'bni';
                break;
            case 'bri_va':
                $method  = 'bri';
                $channel = 'bri';
                break;
            case 'permata_va':
                $method  = 'permata';
                $channel = 'permata';
                break;
            default:
                $method  = $type;
                $channel = $type;
        }

        return ['method' => $method, 'channel' => $channel];
    }

    public function getClientKey(): string
    {
        return $this->clientKey;
    }

    public function isProduction(): bool
    {
        return $this->isProduction;
    }
}
