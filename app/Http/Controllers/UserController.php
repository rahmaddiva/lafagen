<?php

namespace App\Http\Controllers;

use App\Enums\Community;
use App\Enums\UserRole;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    private function community(Request $request): Community
    {
        return $request->attributes->get('community');
    }

    private function findInCommunity(Request $request, int $id): User
    {
        return User::query()
            ->where('community', $this->community($request)->value)
            ->findOrFail($id);
    }

    public function index(Request $request): Response
    {
        $community = $this->community($request);

        $users = User::query()
            ->where('community', $community->value)
            ->withCount('reports')
            ->orderBy('role')
            ->orderBy('name')
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role->value,
                'reports_count' => $u->reports_count,
                'created_at' => $u->created_at->format('d M Y'),
            ])->all();

        return Inertia::render('Users/Index', [
            'title' => 'Pengguna',
            'community' => $community->config(),
            'users' => $users,
            'current_user_id' => $request->user()->id,
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $community = $this->community($request);
        $data = $request->validated();

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // cast hashed
            'role' => UserRole::from($data['role']),
            'community' => $community,
        ]);

        return redirect('/'.$community->value.'/users')
            ->with('success', 'Pengguna ditambahkan.');
    }

    public function update(UpdateUserRequest $request, string $communityParam, int $user): RedirectResponse
    {
        $community = $this->community($request);
        $data = $request->validated();
        $target = $this->findInCommunity($request, $user);

        if ($target->id === $request->user()->id && $target->isAdmin() && $data['role'] !== 'admin') {
            return redirect('/'.$community->value.'/users')
                ->with('error', 'Anda tidak bisa menurunkan role admin pada diri sendiri.');
        }

        $target->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => UserRole::from($data['role']),
        ]);

        if (! empty($data['password'])) {
            $target->update(['password' => $data['password']]);
        }

        return redirect('/'.$community->value.'/users')
            ->with('success', 'Pengguna diperbarui.');
    }

    public function destroy(Request $request, string $communityParam, int $user): RedirectResponse
    {
        $community = $this->community($request);
        $target = $this->findInCommunity($request, $user);

        if ($target->id === $request->user()->id) {
            return redirect('/'.$community->value.'/users')
                ->with('error', 'Anda tidak bisa menghapus akun sendiri.');
        }

        if ($target->isAdmin()) {
            $adminCount = User::query()
                ->where('community', $community->value)
                ->where('role', UserRole::ADMIN->value)
                ->count();
            if ($adminCount <= 1) {
                return redirect('/'.$community->value.'/users')
                    ->with('error', 'Admin terakhir komunitas tidak bisa dihapus.');
            }
        }

        $target->delete();

        return redirect('/'.$community->value.'/users')
            ->with('success', 'Pengguna dihapus.');
    }
}