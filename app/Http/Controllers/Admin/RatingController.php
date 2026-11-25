<?php

namespace App\Http\Controllers\Admin;

use App\Models\Rating;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RatingController extends AdminController
{
    /** GET /admin/ratings - filtros opcionales ?game_id= y ?rated_id= (jugador valorado). */
    public function index(Request $request)
    {
        $ratings = Rating::query()
            ->with(['user:id,name', 'rated:id,name'])
            ->when($request->game_id, fn ($query, $id) => $query->where('game_id', $id))
            ->when($request->rated_id, fn ($query, $id) => $query->where('rated_id', $id))
            ->orderBy('id')
            ->paginate($this->perPage($request));

        return response()->json($ratings);
    }

    /** GET /admin/ratings/{rating} */
    public function show(Rating $rating): JsonResponse
    {
        return response()->json($rating->load(['user:id,name', 'rated:id,name', 'game']));
    }

    /** DELETE /admin/ratings/{rating} - elimina una valoracion injusta o inapropiada. */
    public function destroy(Rating $rating): Response
    {
        $rating->delete();

        return response()->noContent();
    }
}
