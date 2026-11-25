<?php

namespace App\Http\Controllers\Admin;

use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class GameController extends AdminController
{
    /** GET /admin/games - filtros opcionales ?status=, ?venue_id= y ?date=YYYY-MM-DD. */
    public function index(Request $request)
    {
        $games = Game::query()
            ->with(['user:id,name', 'venue:id,name,city'])
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->when($request->venue_id, fn ($query, $venueId) => $query->where('venue_id', $venueId))
            ->when($request->date, fn ($query, $date) => $query->whereDate('date', $date))
            ->orderBy('date')
            ->paginate($this->perPage($request));

        return response()->json($games);
    }

    /** POST /admin/games - crea un partido (organizador: el admin si no se indica user_id). */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['sometimes', 'exists:users,id'],
            'venue_id' => ['required', 'exists:venues,id'],
            'date' => ['required', 'date'],
            'max_players' => ['required', 'integer', 'min:2'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(['open', 'full', 'cancelled'])],
        ]);
        $data['user_id'] ??= $request->user()->id;

        return response()->json(Game::create($data), 201);
    }

    /** GET /admin/games/{game} - con organizador, campo e inscripciones. */
    public function show(Game $game): JsonResponse
    {
        return response()->json($game->load(['user', 'venue', 'registrations.user']));
    }

    /** PUT /admin/games/{game} */
    public function update(Request $request, Game $game): JsonResponse
    {
        $data = $request->validate([
            'venue_id' => ['sometimes', 'required', 'exists:venues,id'],
            'date' => ['sometimes', 'required', 'date'],
            'max_players' => ['sometimes', 'required', 'integer', 'min:2'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'required', Rule::in(['open', 'full', 'cancelled'])],
        ]);

        $game->update($data);

        return response()->json($game);
    }

    /** PATCH /admin/games/{game}/cancel */
    public function cancel(Game $game): JsonResponse
    {
        $game->update(['status' => 'cancelled']);

        return response()->json($game);
    }

    /** DELETE /admin/games/{game} - borra el partido y, en cascada, inscripciones y valoraciones. */
    public function destroy(Game $game): Response
    {
        $game->delete();

        return response()->noContent();
    }
}
