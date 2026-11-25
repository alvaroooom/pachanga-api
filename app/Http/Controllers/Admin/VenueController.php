<?php

namespace App\Http\Controllers\Admin;

use App\Models\Venue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class VenueController extends AdminController
{
    private const SURFACES = ['artificial', 'natural', 'indoor'];

    /** GET /admin/venues - lista paginada, filtro opcional ?city=. */
    public function index(Request $request)
    {
        $venues = Venue::query()
            ->when($request->city, fn ($query, $city) => $query->where('city', $city))
            ->orderBy('id')
            ->paginate($this->perPage($request));

        return response()->json($venues);
    }

    /** POST /admin/venues */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'surface' => ['required', Rule::in(self::SURFACES)],
        ]);

        return response()->json(Venue::create($data), 201);
    }

    /** GET /admin/venues/{venue} */
    public function show(Venue $venue): JsonResponse
    {
        return response()->json($venue);
    }

    /** PUT /admin/venues/{venue} */
    public function update(Request $request, Venue $venue): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'address' => ['sometimes', 'required', 'string', 'max:255'],
            'city' => ['sometimes', 'required', 'string', 'max:255'],
            'surface' => ['sometimes', 'required', Rule::in(self::SURFACES)],
        ]);

        $venue->update($data);

        return response()->json($venue);
    }

    /** DELETE /admin/venues/{venue} - borra el campo y, en cascada, sus partidos. */
    public function destroy(Venue $venue): Response
    {
        $venue->delete();

        return response()->noContent();
    }
}
