<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('attendance:make-admin {email : The registered user email address}')]
#[Description('Promote a registered user to attendance administrator')]
class MakeUserAdmin extends Command
{
    public function handle(): int
    {
        $user = User::query()->where('email', $this->argument('email'))->first();

        if ($user === null) {
            $this->error('No user was found with that email address.');

            return self::FAILURE;
        }

        $user->is_admin = true;
        $user->save();

        $this->info("{$user->email} is now an administrator.");

        return self::SUCCESS;
    }
}
