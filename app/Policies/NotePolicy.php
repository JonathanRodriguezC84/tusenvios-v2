<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;

class NotePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->accountIsActive();
    }

    public function create(User $user): bool
    {
        return $user->accountIsActive();
    }

    public function update(User $user, Note $note): bool
    {
        return $user->accountIsActive() && $note->user_id === $user->id;
    }

    public function delete(User $user, Note $note): bool
    {
        return $user->accountIsActive() && $note->user_id === $user->id;
    }
}
