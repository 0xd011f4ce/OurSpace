<?php

namespace App\Actions;

use GuzzleHttp\Client;

use App\Types\TypeActor;
use Illuminate\Support\Facades\Log;

class ActionsUser
{
    public static function update_profile ()
    {
        if (!auth ()->check ())
            return ["error" => "You must be logged in to update your profile."];

        $user = auth ()->user ();
        try {
            $client = new Client ();
            $response = $client->post ($user->actor->outbox, [
                "json" => [
                    "type" => "UpdateProfile"
                ]
            ]);
        } catch (\Exception $e)
        {
            Log::error ("Error updating profile: " . $e->getMessage ());
            return ["error" => "Error updating profile"];
        }

        return ["success" => "Profile updated"];
    }
}
