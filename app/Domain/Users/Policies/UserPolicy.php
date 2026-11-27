<?php

namespace App\Domain\Users\Policies;

use App\Domain\Users\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Profiles are private until public profiles (with a privacy setting)
     * land: only the owner sees theirs. Others get a 404, so the page
     * doesn't confirm which usernames exist.
     */
    public function viewProfile(User $viewer, User $owner): Response
    {
        return $viewer->is($owner) ? Response::allow() : Response::denyAsNotFound();
    }
}
