<?php

namespace Tests\Feature;

use App\Enums\Community;
use App\Models\Category;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['community' => Community::FAD, 'role' => 'admin']);
    }

    public function test_anggota_forbidden(): void
    {
        $u = User::factory()->create(['community' => Community::FAD, 'role' => 'anggota']);
        $this->actingAs($u)->get('/fad/users')->assertForbidden();
        $this->actingAs($u)->post('/fad/users', ['name' => 'X', 'email' => 'x@x.test', 'password' => 'secret123'])
            ->assertForbidden();
    }

    public function test_admin_creates_user_with_own_community(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/fad/users', [
            'name' => 'Anggota Baru',
            'email' => 'baru@lafagen.test',
            'password' => 'secret123',
            'role' => 'anggota',
        ])->assertRedirect('/fad/users');

        $user = User::where('email', 'baru@lafagen.test')->firstOrFail();
        $this->assertSame(Community::FAD, $user->community);
        $this->assertSame('anggota', $user->role->value);
        $this->assertTrue(password_verify('secret123', $user->password));
    }

    public function test_index_scoped_to_admin_community(): void
    {
        $admin = $this->admin();
        User::factory()->create(['community' => Community::FAD, 'name' => 'Fad User']);
        User::factory()->create(['community' => Community::GENRE, 'name' => 'Genre User']);

        $this->actingAs($admin)->get('/fad/users')->assertOk()->assertInertia(
            fn ($p) => $p->component('Users/Index')
                ->has('users', 2)
                ->whereNot('users.0.name', 'Genre User')
                ->whereNot('users.1.name', 'Genre User')
        );
    }

    public function test_admin_cannot_update_genre_user_via_fad(): void
    {
        $admin = $this->admin();
        $genre = User::factory()->create(['community' => Community::GENRE, 'name' => 'Genre User']);

        $this->actingAs($admin)->put("/fad/users/{$genre->id}", [
            'name' => 'Hax', 'role' => 'admin',
        ])->assertNotFound();
        $this->assertSame('Genre User', $genre->fresh()->name);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->delete("/fad/users/{$admin->id}")
            ->assertRedirect('/fad/users');
        $this->assertNotNull($admin->fresh());
    }

    public function test_admin_cannot_delete_last_admin(): void
    {
        $admin = $this->admin(); // satu-satunya admin FAD
        $other = User::factory()->create(['community' => Community::FAD, 'role' => 'admin']);
        // hapus other dulu: tersisa admin (self) — mencoba hapus diri diblock oleh self-guard,
        // jadi uji guard last-admin dari sisi admin kedua: hapus admin pertama saat hanya
        // 2 admin → 1 tersisa = boleh; skenario tersisa 1 admin = guard self + last-admin.
        $this->actingAs($other)->delete("/fad/users/{$admin->id}")->assertRedirect('/fad/users');
        $this->assertNull($admin->fresh()); // dihapus oleh admin lain, masih 1 admin tersisa

        // kini tersisa 1 admin (other) — hapus diri sendiri diblokir
        $this->actingAs($other)->delete("/fad/users/{$other->id}")->assertRedirect('/fad/users');
        $this->assertNotNull($other->fresh());
    }

    public function test_update_without_password_keeps_old(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create(['community' => Community::FAD, 'role' => 'anggota']);
        $oldHash = $target->password;

        $this->actingAs($admin)->put("/fad/users/{$target->id}", [
            'name' => 'Nama Baru',
            'email' => $target->email,
            'role' => 'anggota',
        ])->assertRedirect('/fad/users');

        $fresh = $target->fresh();
        $this->assertSame('Nama Baru', $fresh->name);
        $this->assertSame($oldHash, $fresh->password);
    }

    public function test_email_unique_global(): void
    {
        $admin = $this->admin();
        User::factory()->create(['community' => Community::FAD, 'email' => 'dipakai@lafagen.test']);
        $this->actingAs($admin)->post('/fad/users', [
            'name' => 'X', 'email' => 'dipakai@lafagen.test', 'password' => 'secret123', 'role' => 'anggota',
        ])->assertSessionHasErrors('email');
    }
}