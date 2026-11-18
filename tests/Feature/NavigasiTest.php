<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the full admin menu including the stock log', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('href="/riwayat-stok"', false)
        ->assertSee('href="/admin/laporan"', false)
        ->assertSee('href="/panduan"', false)
        ->assertDontSee('href="/petugas/barang-masuk"', false);
});

it('shows only petugas menu items to a petugas', function () {
    $petugas = User::factory()->create(['role' => 'petugas']);

    $this->actingAs($petugas)
        ->get(route('petugas.dashboard'))
        ->assertOk()
        ->assertSee('href="/petugas/barang-masuk"', false)
        ->assertSee('href="/petugas/riwayat-stok"', false)
        ->assertDontSee('href="/admin/laporan"', false);
});

it('marks the current page in the sidebar and offers a skip link', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('riwayat-stok.index'))
        ->assertOk()
        ->assertSee('aria-current="page"', false)
        ->assertSee('href="#main-content"', false)
        ->assertSee('id="main-content"', false);
});
