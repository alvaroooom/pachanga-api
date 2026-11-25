<?php

namespace App\Http\Controllers\Admin;

use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PostController extends AdminController
{
    /** GET /admin/posts - filtro opcional ?user_id=. */
    public function index(Request $request)
    {
        $posts = Post::query()
            ->with('user:id,name')
            ->withCount('comments')
            ->when($request->user_id, fn ($query, $id) => $query->where('user_id', $id))
            ->latest('id')
            ->paginate($this->perPage($request));

        return response()->json($posts);
    }

    /** POST /admin/posts - crea un post (autor: el admin si no se indica user_id). */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['sometimes', 'exists:users,id'],
            'content' => ['required', 'string'],
        ]);
        $data['user_id'] ??= $request->user()->id;

        return response()->json(Post::create($data), 201);
    }

    /** GET /admin/posts/{post} - con sus comentarios. */
    public function show(Post $post): JsonResponse
    {
        return response()->json($post->load(['user:id,name', 'comments.user:id,name']));
    }

    /** PUT /admin/posts/{post} - edita el contenido. */
    public function update(Request $request, Post $post): JsonResponse
    {
        $data = $request->validate(['content' => ['required', 'string']]);
        $post->update($data);

        return response()->json($post);
    }

    /** DELETE /admin/posts/{post} - borra el post y, en cascada, sus comentarios. */
    public function destroy(Post $post): Response
    {
        $post->delete();

        return response()->noContent();
    }
}
