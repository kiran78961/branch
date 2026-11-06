<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class UpdateUserProfilePicture
{
    public function handle(User $user, UploadedFile $picture): void
    {
        $storedPath = $picture->storePublicly("profile-pictures/{$user->getKey()}", 'public');

        if ($storedPath === false) {
            throw new RuntimeException('The profile picture could not be stored.');
        }

        $previousPath = $user->avatar_path;
        $user->avatar_path = $storedPath;
        $user->save();

        if ($previousPath !== null && $previousPath !== $storedPath && Storage::disk('public')->exists($previousPath)) {
            if (! Storage::disk('public')->delete($previousPath)) {
                Log::warning('A replaced profile picture could not be deleted.', [
                    'user_id' => $user->getKey(),
                    'avatar_path' => $previousPath,
                ]);
            }
        }
    }
}
