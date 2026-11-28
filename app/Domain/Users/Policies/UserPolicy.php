<?php

namespace App\Domain\Users\Policies;

use App\Domain\Users\Models\User;

class UserPolicy
{
    /**
     * Covers the three profile sections (profile, journal, library): any
     * signed-in user may look at anyone's. Guests are kept out by the
     * routes' auth middleware, and unknown usernames are a 404 from
     * route-model binding, so nothing here confirms who exists.
     *
     * A per-user privacy setting (and guest access) will narrow this down;
     * this is the single place it has to change.
     */
    public function viewProfile(User $viewer, User $owner): bool
    {
        return true;
    }
}
