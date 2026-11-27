<?php

namespace App\Http\Controllers\Settings;

use App\Domain\Users\Actions\UpdateProfileAction;
use App\Domain\Users\Models\User;
use App\Domain\Users\Support\Username;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        return Inertia::render('settings/profile', [
            'profile' => $user->only(['name', 'username', 'email']),
            'rules' => [
                'name_max' => UpdateProfileRequest::NAME_MAX_LENGTH,
                'username' => [
                    'min' => Username::MIN_LENGTH,
                    'max' => Username::MAX_LENGTH,
                ],
            ],
        ]);
    }

    public function update(UpdateProfileRequest $request, UpdateProfileAction $updateProfile): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $updateProfile->execute(
            $user,
            $request->string('name')->toString(),
            $request->string('username')->toString(),
        );

        Inertia::flash('success', __('ui.settings.saved'));

        return redirect()->route('settings.profile');
    }
}
