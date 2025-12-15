<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Pasien;
use App\Models\Dokter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test - Register Pasien
     * POST /api/v1/auth/register
     */
    public function test_register_pasien_success()
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Ahmad Zaki',
            'email' => 'ahmad@example.com',
            'password' => 'Password123!@#',
            'password_confirmation' => 'Password123!@#',
            'role' => 'pasien',
            'nomor_identitas' => '1234567890123456',
            'tanggal_lahir' => '1990-01-15',
            'jenis_kelamin' => 'laki-laki',
            'alamat' => 'Jl. Merdeka No. 123',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'pesan',
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role',
                ],
                'token',
            ],
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'ahmad@example.com',
            'role' => 'pasien',
        ]);

        $this->assertDatabaseHas('patients', [
            'nomor_identitas' => '1234567890123456',
        ]);
    }

    /**
     * Test - Register Dokter
     */
    public function test_register_dokter_success()
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Dr. Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'Password123!@#',
            'password_confirmation' => 'Password123!@#',
            'role' => 'dokter',
            'nomor_identitas' => '1234567890123457',
            'spesialisasi' => 'Kardiologi',
            'nomor_praktik' => 'SIP123456',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('doctors', [
            'spesialisasi' => 'Kardiologi',
        ]);
    }

    /**
     * Test - Register dengan email yang sudah terdaftar
     */
    public function test_register_duplicate_email()
    {
        User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'New User',
            'email' => 'existing@example.com',
            'password' => 'Password123!@#',
            'password_confirmation' => 'Password123!@#',
            'role' => 'pasien',
            'nomor_identitas' => '1234567890123456',
            'tanggal_lahir' => '1990-01-15',
            'jenis_kelamin' => 'laki-laki',
            'alamat' => 'Jl. Merdeka No. 123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * Test - Login dengan email dan password
     */
    public function test_login_success()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('Password123!@#'),
            'role' => 'pasien',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'Password123!@#',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'pesan',
            'data' => [
                'user' => ['id', 'name', 'email', 'role'],
                'token',
            ],
        ]);

        $this->assertTrue($response->json('success'));
    }

    /**
     * Test - Login dengan email salah
     */
    public function test_login_invalid_email()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'Password123!@#',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'pesan' => 'Email atau password salah',
        ]);
    }

    /**
     * Test - Login dengan password salah
     */
    public function test_login_invalid_password()
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('CorrectPassword123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'WrongPassword123',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test - Get profil user yang terautentikasi
     */
    public function test_get_profile_authenticated()
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => 'pasien']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'pesan',
            'data' => [
                'id',
                'name',
                'email',
                'role',
            ],
        ]);
    }

    /**
     * Test - Get profil tanpa autentikasi
     */
    public function test_get_profile_unauthenticated()
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    }

    /**
     * Test - Logout (invalidate token)
     */
    public function test_logout_success()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'pesan' => 'Logout berhasil',
        ]);
    }

    /**
     * Test - Refresh token
     */
    public function test_refresh_token()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/auth/refresh');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'pesan',
            'data' => [
                'token',
            ],
        ]);
    }

    /**
     * Test - Register dengan password yang lemah
     */
    public function test_register_weak_password()
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => 'weak',
            'password_confirmation' => 'weak',
            'role' => 'pasien',
            'nomor_identitas' => '1234567890123456',
            'tanggal_lahir' => '1990-01-15',
            'jenis_kelamin' => 'laki-laki',
            'alamat' => 'Jl. Merdeka',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }
}
