<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class UserController extends AdminController
{
    /** GET /admin/users - lista paginada, con busqueda ?search= por nombre o email. */
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('id')
            ->paginate($this->perPage($request));

        return response()->json($users);
    }

    /** POST /admin/users - crea un usuario. */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['sometimes', Rule::in(['admin', 'user', 'guest'])],
        ]);

        $user = User::create($request->only('name', 'email', 'password'));
        if (isset($data['role'])) {
            $user->role = $data['role'];
            $user->save();
        }

        return response()->json($user->fresh(), 201);
    }

    /** GET /admin/users/{user} */
    public function show(User $user): JsonResponse
    {
        return response()->json($user);
    }

    /** PUT /admin/users/{user} - edita nombre y email. */
    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update($data);

        return response()->json($user);
    }

    /** PATCH /admin/users/{user}/role - asigna el rol (admin, user o guest). */
    public function role(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(['admin', 'user', 'guest'])],
        ]);

        if ($user->is($request->user())) {
            return response()->json(['message' => 'No puedes cambiar tu propio rol.'], 422);
        }

        $user->role = $data['role'];
        $user->save();

        return response()->json($user);
    }

    /** DELETE /admin/users/{user} - borra al usuario y, en cascada, sus datos. */
    public function destroy(Request $request, User $user): Response|JsonResponse
    {
        if ($user->is($request->user())) {
            return response()->json(['message' => 'No puedes borrar tu propia cuenta.'], 422);
        }

        $user->delete();

        return response()->noContent();
    }
}
