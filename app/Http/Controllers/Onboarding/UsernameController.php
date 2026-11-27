<?php

namespace App\Http\Controllers\Onboarding;

use App\Domain\Users\Actions\SetUsernameAction;
use App\Domain\Users\Actions\SuggestUsernameAction;
use App\Domain\Users\Models\User;
use App\Domain\Users\Support\Username;
use App\Http\Controllers\Controller;
use App\Http\Requests\Onboarding\ChooseUsernameRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UsernameController extends Controller
{
    public function show(Request $request, SuggestUsernameAction $suggest): Response|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasUsername()) {
            return redirect()->route('home');
        }

        return Inertia::render('onboarding/username', [
            'suggestion' => $suggest->execute($user),
            'rules' => [
                'min' => Username::MIN_LENGTH,
                'max' => Username::MAX_LENGTH,
            ],
        ]);
    }

    public function store(ChooseUsernameRequest $request, SetUsernameAction $setUsername): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $setUsername->execute($user, $request->string('username')->toString());

        Inertia::flash('success', __('ui.onboarding.done', ['username' => $user->username]));

        return redirect()->intended(route('home'));
    }
}
