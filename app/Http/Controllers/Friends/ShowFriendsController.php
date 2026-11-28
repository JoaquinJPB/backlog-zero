<?php

namespace App\Http\Controllers\Friends;

use App\Domain\Users\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Everyone with an account, so the group can reach each other's profile,
 * journal and library. There is no follow model: the app is used by one
 * group of friends, so having an account is the whole membership.
 */
class ShowFriendsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        /** @var User $viewer */
        $viewer = $request->user();

        $friends = User::query()
            ->whereNotNull('username')
            ->whereKeyNot($viewer->id)
            ->orderBy('username')
            ->get(['id', 'name', 'username', 'avatar_url']);

        return Inertia::render('friends/index', [
            'friends' => $friends->map(fn (User $user): array => [
                'name' => $user->name,
                'username' => $user->username,
                'avatar_url' => $user->avatar_url,
            ])->all(),
        ]);
    }
}
