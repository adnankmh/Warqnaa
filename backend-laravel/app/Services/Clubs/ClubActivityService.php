<?php

namespace App\Services\Clubs;

use App\Models\{Club,ClubActivityLog,User};

class ClubActivityService
{
    public function record(Club $club, ?User $actor, string $category, string $action, string $description, array $meta = [], ?User $subject = null): ClubActivityLog
    {
        return ClubActivityLog::create([
            'club_id'=>$club->id,
            'actor_id'=>$actor?->id,
            'subject_user_id'=>$subject?->id,
            'category'=>$category,
            'action'=>$action,
            'description'=>$description,
            'meta'=>$meta ?: null,
        ]);
    }
}
