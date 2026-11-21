<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the user guide to guests with a login link', function () {
    $this->get('/panduan')
        ->assertOk()
        ->assertSeeText('Panduan Penggunaan Sistem Inventaris Gudang')
        ->assertSeeText('Panduan Administrator')
        ->assertSeeText('Panduan Petugas Gudang')
        ->assertSeeText('Tanya jawab')
        ->assertSee('href="/login"', false);
});

it('links an admin back to the admin dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get('/panduan')
        ->assertOk()
        ->assertSee('href="/admin/dashboard"', false);
});

it('links a petugas back to the petugas dashboard', function () {
    $petugas = User::factory()->create(['role' => 'petugas']);

    $this->actingAs($petugas)
        ->get('/panduan')
        ->assertOk()
        ->assertSee('href="/petugas/dashboard"', false);
});
