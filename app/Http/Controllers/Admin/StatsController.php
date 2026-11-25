<?php

namespace App\Http\Controllers\Admin;

use App\Models\Comment;
use App\Models\Game;
use App\Models\Post;
use App\Models\Rating;
use App\Models\Registration;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Http\JsonResponse;

class StatsController extends AdminController
{
    /** GET /admin/stats - contadores del cuadro de mando. */
    public function index(): JsonResponse
    {
        $byStatus = Game::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        return response()->json([
            'users' => User::count(),
            'venues' => Venue::count(),
            'games' => Game::count(),
            'games_by_status' => array_merge(['open' => 0, 'full' => 0, 'cancelled' => 0], $byStatus),
            'registrations' => Registration::count(),
            'ratings' => Rating::count(),
            'ratings_avg' => round((float) Rating::avg('score'), 2),
            'posts' => Post::count(),
            'comments' => Comment::count(),
        ]);
    }
}
