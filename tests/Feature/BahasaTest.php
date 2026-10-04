<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BahasaTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_bawaan_bahasa_indonesia(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('<html lang="id">', false)
            ->assertSee('Beranda')
            ->assertSee(route('bahasa', 'en'), false);
    }

    public function test_tombol_en_mengganti_halaman_publik_ke_inggris(): void
    {
        $this->from('/dosen')->get('/bahasa/en')->assertRedirect('/dosen');

        $this->get('/')->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('Home')
            ->assertSee('Discover IT Program')
            ->assertDontSee('Kenali Prodi TI');

        $this->get('/mata-kuliah')->assertOk()->assertSee('Courses');
        $this->get('/login')->assertOk()->assertSee('Sign In to the System');
    }

    public function test_tombol_id_kembali_ke_bahasa_indonesia(): void
    {
        $this->get('/bahasa/en');
        $this->get('/bahasa/id');

        $this->get('/')->assertOk()->assertSee('Kenali Prodi TI')->assertDontSee('Discover IT Program');
    }

    public function test_dashboard_tetap_bahasa_indonesia(): void
    {
        $this->get('/bahasa/en');

        $staff = User::where('role', 'staff')->firstOrFail();
        $this->actingAs($staff)->get('/staff-dashboard')->assertOk()->assertSee('Dashboard');
        $this->assertSame('id', app()->getLocale());
    }

    public function test_bahasa_tidak_dikenal_ditolak(): void
    {
        $this->get('/bahasa/fr')->assertNotFound();
    }

    public function test_pesan_login_ikut_bahasa_inggris(): void
    {
        $this->get('/bahasa/en');

        $this->from('/login')->post('/login', ['role' => 'staff', 'email' => 'salah@politala.ac.id', 'password' => 'salahsekali'])
            ->assertSessionHasErrors(['email' => 'Incorrect email or password.']);
    }
}
