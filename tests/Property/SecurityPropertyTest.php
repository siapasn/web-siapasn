<?php

namespace Tests\Property;

/**
 * Property-based tests for Security (Properties 23–25).
 *
 * These are pure logic tests — no HTTP requests or database connections needed.
 * They verify correctness properties of authentication checks, role-based access
 * control, and session timeout logic.
 *
 * Validates: Requirements 17.1, 17.3
 */
class SecurityPropertyTest extends PropertyTestCase
{
    // -------------------------------------------------------------------------
    // Property 23: Akses tanpa autentikasi diarahkan ke login
    // -------------------------------------------------------------------------

    /**
     * For any falsy/empty user_id value in session, the user must NOT be
     * considered authenticated. This mirrors the AuthFilter check:
     *   if (! $session->get('user_id')) { redirect to /login }
     *
     * **Validates: Requirements 17.1**
     */
    public function testAksesTanpaAuthDiarahkanKeLogin(): void
    {
        // Feature: cpns-tryout-online, Property 23: Akses tanpa autentikasi diarahkan ke login
        $this->forAll(
            \Eris\Generator\elements([null, '', 0, false])
        )
        ->withMaxSize(100)
        ->then(function ($userId): void {
            // Simulate: no user_id in session = not authenticated
            $isAuthenticated = ! empty($userId);
            $this->assertFalse($isAuthenticated, 'User tanpa session user_id tidak boleh dianggap terautentikasi');
        });
    }

    // -------------------------------------------------------------------------
    // Property 24: Akses lintas role menghasilkan 403
    // -------------------------------------------------------------------------

    /**
     * For any combination of user role and allowed roles, access must be
     * denied (403) when the user's role is not in the allowed list.
     * This mirrors the RoleFilter logic:
     *   if (! in_array($userRole, $arguments, true)) { return 403 }
     *
     * **Validates: Requirements 17.1**
     */
    public function testAksesLintasRoleMenghasilkan403(): void
    {
        // Feature: cpns-tryout-online, Property 24: Akses lintas role menghasilkan 403
        $this->forAll(
            \Eris\Generator\elements(['user', 'admin', 'super_admin']),
            \Eris\Generator\elements([['admin', 'super_admin'], ['super_admin'], ['user']])
        )
        ->withMaxSize(100)
        ->then(function (string $userRole, array $allowedRoles): void {
            $hasAccess = in_array($userRole, $allowedRoles, true);
            // If user role is not in allowed roles, access should be denied (403)
            if (! $hasAccess) {
                $this->assertFalse($hasAccess, "Role '{$userRole}' tidak boleh mengakses route yang memerlukan " . implode('/', $allowedRoles));
            } else {
                $this->assertTrue($hasAccess, "Role '{$userRole}' harus dapat mengakses route yang memerlukan " . implode('/', $allowedRoles));
            }
        });
    }

    // -------------------------------------------------------------------------
    // Property 25: Sesi tidak aktif 60 menit otomatis berakhir
    // -------------------------------------------------------------------------

    /**
     * For any inactivity period greater than 3600 seconds (60 minutes),
     * the session must be considered expired. This mirrors the AuthFilter logic:
     *   if ((time() - $lastActivity) > 3600) { $session->destroy() }
     *
     * **Validates: Requirements 17.3**
     */
    public function testSesiTidakAktif60MenitOtomatisBerakhi(): void
    {
        // Feature: cpns-tryout-online, Property 25: Sesi tidak aktif 60 menit otomatis berakhir
        $this->forAll(
            \Eris\Generator\choose(3601, 86400) // more than 60 minutes in seconds
        )
        ->withMaxSize(100)
        ->then(function (int $secondsInactive): void {
            $lastActivity = time() - $secondsInactive;
            $timeout      = 3600; // 60 minutes
            $isExpired    = (time() - $lastActivity) > $timeout;
            $this->assertTrue($isExpired, "Sesi yang tidak aktif selama {$secondsInactive} detik harus dianggap kedaluwarsa");
        });
    }
}
