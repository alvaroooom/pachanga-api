<?php

namespace App\Http\Controllers\Admin;

use App\Models\Game;
use App\Models\Registration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class RegistrationController extends AdminController
{
    /** GET /admin/games/{game}/registrations - jugadores apuntados a un partido. */
    public function index(Request $request, Game $game)
    {
        $registrations = $game->registrations()
            ->with('user:id,name,email')
            ->orderBy('id')
            ->paginate($this->perPage($request));

        return response()->json($registrations);
    }

    /** PATCH /admin/registrations/{registration} - cambia el estado (confirmed / waitlist). */
    public function update(Request $request, Registration $registration): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['confirmed', 'waitlist'])],
        ]);

        $registration->update($data);

        return response()->json($registration);
    }

    /** DELETE /admin/registrations/{registration} - expulsa al jugador del partido. */
    public function destroy(Registration $registration): Response
    {
        $registration->delete();

        return response()->noContent();
    }
}
