<?php

namespace App\Http\Controllers\Admin;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CommentController extends AdminController
{
    /** GET /admin/comments - filtro opcional ?post_id=. */
    public function index(Request $request)
    {
        $comments = Comment::query()
            ->with('user:id,name')
            ->when($request->post_id, fn ($query, $id) => $query->where('post_id', $id))
            ->latest('id')
            ->paginate($this->perPage($request));

        return response()->json($comments);
    }

    /** DELETE /admin/comments/{comment} */
    public function destroy(Comment $comment): Response
    {
        $comment->delete();

        return response()->noContent();
    }
}
