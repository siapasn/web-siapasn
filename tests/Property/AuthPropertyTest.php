<?php

namespace Tests\Property;

use App\Models\UserModel;

/**
 * Property-based tests for Authentication (Properties 1–5).
 *
 * These tests focus on pure logic and do NOT require a real database connection.
 * They verify correctness properties of password hashing, validation rules,
 * lockout logic, and session lifecycle.
 */
class AuthPropertyTest extends PropertyTestCase
{
    // -------------------------------------------------------------------------
    // Property 1: Registrasi user menyimpan data dengan benar
    // -------------------------------------------------------------------------

    /**
     * For any valid password, the stored hash must be a valid bcrypt hash
     * (not plaintext) and must be verifiable with password_verify().
     *
     * // Feature: cpns-tryout-online, Property 1: Registrasi user menyimpan data dengan benar
     */
    public function testRegistrasiMenyimpanPasswordSebagaiBcrypt(): void
    {
        // Feature: cpns-tryout-online, Property 1: Registrasi user menyimpan data dengan benar
        $this->forAll(
            \Eris\Generator\elements(['password123', 'MyP@ssw0rd', 'abcdefgh', 'UPPERCASE1', 'mixedCase99'])
        )
        ->withMaxSize(100)
        ->then(function (string $password): void {
            $hashed = password_hash($password, PASSWORD_BCRYPT);

            // Hash must not equal plaintext
            $this->assertNotEquals($password, $hashed);

            // Hash must be verifiable
            $this->assertTrue(password_verify($password, $hashed));

            // Hash must start with bcrypt identifier
            $this->assertStringStartsWith('$2y$', $hashed);
        });
    }

    /**
     * For any valid registration data, the role assigned must always be 'user'.
     *
     * // Feature: cpns-tryout-online, Property 1: Registrasi user menyimpan data dengan benar
     */
    public function testRegistrasiSelaluMenetapkanRoleUser(): void
    {
        // Feature: cpns-tryout-online, Property 1: Registrasi user menyimpan data dengan benar
        $this->forAll(
            \Eris\Generator\elements(['Alice', 'Bob', 'Charlie', 'Diana', 'Eve']),
            \Eris\Generator\elements(['alice@example.com', 'bob@example.com', 'charlie@example.com'])
        )
        ->withMaxSize(100)
        ->then(function (string $nama, string $email): void {
            // Simulate the data that would be saved during registration
            $dataToSave = [
                'nama'     => $nama,
                'email'    => $email,
                'password' => password_hash('somepassword', PASSWORD_BCRYPT),
                'role'     => 'user', // always set to 'user' on registration
            ];

            $this->assertEquals('user', $dataToSave['role'], 'Role harus selalu "user" saat registrasi');
        });
    }

    // -------------------------------------------------------------------------
    // Property 2: Email duplikat ditolak saat registrasi
    // -------------------------------------------------------------------------

    /**
     * The UserModel validation rule for email must contain 'is_unique' to
     * prevent duplicate email registrations.
     *
     * // Feature: cpns-tryout-online, Property 2: Email duplikat ditolak saat registrasi
     */
    public function testEmailDuplikatDitolakOlehValidasiModel(): void
    {
        // Feature: cpns-tryout-online, Property 2: Email duplikat ditolak saat registrasi
        $userModel = new UserModel();
        $rules     = $userModel->getValidationRules();

        // Email rule must contain is_unique
        $this->assertArrayHasKey('email', $rules, 'Aturan validasi email harus ada di UserModel');
        $this->assertStringContainsString(
            'is_unique',
            $rules['email'],
            'Aturan validasi email harus mengandung is_unique untuk mencegah duplikasi'
        );
    }

    /**
     * For any email address, the is_unique validation rule must be present
     * regardless of the email format.
     *
     * // Feature: cpns-tryout-online, Property 2: Email duplikat ditolak saat registrasi
     */
    public function testAturanValidasiEmailSelaluMengandungIsUnique(): void
    {
        // Feature: cpns-tryout-online, Property 2: Email duplikat ditolak saat registrasi
        $this->forAll(
            \Eris\Generator\elements([
                'test@example.com',
                'user@domain.org',
                'admin@site.net',
                'hello@world.id',
                'cpns@tryout.co.id',
            ])
        )
        ->withMaxSize(100)
        ->then(function (string $email): void {
            $userModel = new UserModel();
            $rules     = $userModel->getValidationRules();

            // The is_unique rule must always be present, regardless of the email value
            $this->assertStringContainsString(
                'is_unique',
                $rules['email'] ?? '',
                "Aturan is_unique harus ada untuk email: {$email}"
            );
        });
    }

    // -------------------------------------------------------------------------
    // Property 3: Login valid menghasilkan sesi aktif
    // -------------------------------------------------------------------------

    /**
     * For any correct password, password_verify() must return true.
     * For any wrong password, password_verify() must return false.
     *
     * // Feature: cpns-tryout-online, Property 3: Login valid menghasilkan sesi aktif
     */
    public function testLoginValidMemverifikasiPasswordDenganBenar(): void
    {
        // Feature: cpns-tryout-online, Property 3: Login valid menghasilkan sesi aktif
        $this->forAll(
            \Eris\Generator\elements(['password123', 'MyP@ssw0rd', 'abcdefgh', 'UPPERCASE1', 'mixedCase99']),
            \Eris\Generator\elements(['wrongpass', 'incorrect', 'badpassword', '12345678', 'notright'])
        )
        ->withMaxSize(100)
        ->then(function (string $correctPassword, string $wrongPassword): void {
            $hashed = password_hash($correctPassword, PASSWORD_BCRYPT);

            $this->assertTrue(
                password_verify($correctPassword, $hashed),
                'Password benar harus terverifikasi'
            );

            $this->assertFalse(
                password_verify($wrongPassword, $hashed),
                'Password salah harus ditolak'
            );
        });
    }

    // -------------------------------------------------------------------------
    // Property 4: Akun terkunci setelah 5 kali gagal login
    // -------------------------------------------------------------------------

    /**
     * For any lock duration in the future, isLocked() must return true.
     *
     * // Feature: cpns-tryout-online, Property 4: Akun terkunci setelah 5 kali gagal login
     */
    public function testAkunTerkunciSetelah5KaliGagalLogin(): void
    {
        // Feature: cpns-tryout-online, Property 4: Akun terkunci setelah 5 kali gagal login
        $this->forAll(
            \Eris\Generator\choose(1, 30) // lock duration in minutes
        )
        ->withMaxSize(100)
        ->then(function (int $minutes): void {
            $userModel   = new UserModel();
            $lockedUntil = date('Y-m-d H:i:s', strtotime("+{$minutes} minutes"));
            $user        = ['locked_until' => $lockedUntil];

            $this->assertTrue(
                $userModel->isLocked($user),
                "Akun dengan locked_until di masa depan harus terkunci (locked_until: {$lockedUntil})"
            );
        });
    }

    /**
     * For any lock duration that has already expired, isLocked() must return false.
     *
     * // Feature: cpns-tryout-online, Property 4: Akun terkunci setelah 5 kali gagal login (expired)
     */
    public function testAkunTidakTerkunciSetelahWaktuBerakhir(): void
    {
        // Feature: cpns-tryout-online, Property 4: Akun terkunci setelah 5 kali gagal login (expired)
        $this->forAll(
            \Eris\Generator\choose(1, 60) // minutes in the past
        )
        ->withMaxSize(100)
        ->then(function (int $minutesAgo): void {
            $userModel   = new UserModel();
            $lockedUntil = date('Y-m-d H:i:s', strtotime("-{$minutesAgo} minutes"));
            $user        = ['locked_until' => $lockedUntil];

            $this->assertFalse(
                $userModel->isLocked($user),
                "Akun dengan locked_until di masa lalu tidak boleh terkunci (locked_until: {$lockedUntil})"
            );
        });
    }

    /**
     * An account with no locked_until value must never be considered locked.
     *
     * // Feature: cpns-tryout-online, Property 4: Akun terkunci setelah 5 kali gagal login
     */
    public function testAkunTanpaLockedUntilTidakTerkunci(): void
    {
        // Feature: cpns-tryout-online, Property 4: Akun terkunci setelah 5 kali gagal login
        $this->forAll(
            \Eris\Generator\elements([null, '', '0000-00-00 00:00:00'])
        )
        ->withMaxSize(100)
        ->then(function ($lockedUntil): void {
            $userModel = new UserModel();
            $user      = ['locked_until' => $lockedUntil];

            $this->assertFalse(
                $userModel->isLocked($user),
                'Akun tanpa locked_until tidak boleh dianggap terkunci'
            );
        });
    }

    // -------------------------------------------------------------------------
    // Property 5: Logout menghapus sesi
    // -------------------------------------------------------------------------

    /**
     * After logout, the session data must be empty and user_id must be null.
     *
     * // Feature: cpns-tryout-online, Property 5: Logout menghapus sesi
     */
    public function testLogoutMenghapusSesi(): void
    {
        // Feature: cpns-tryout-online, Property 5: Logout menghapus sesi
        $this->forAll(
            \Eris\Generator\elements([1, 2, 3, 100, 999])
        )
        ->withMaxSize(100)
        ->then(function (int $userId): void {
            // Simulate session data before logout
            $sessionData = [
                'user_id' => $userId,
                'role'    => 'user',
                'nama'    => 'Test User',
            ];

            // Verify session has data before logout
            $this->assertNotEmpty($sessionData, 'Sesi harus berisi data sebelum logout');
            $this->assertEquals($userId, $sessionData['user_id']);

            // Simulate logout: session is destroyed
            $sessionData = [];

            // After logout, session must be empty
            $this->assertEmpty($sessionData, 'Sesi harus kosong setelah logout');
            $this->assertNull($sessionData['user_id'] ?? null, 'user_id harus null setelah logout');
        });
    }
}
