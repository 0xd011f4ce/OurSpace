<?php

namespace App\Http\Controllers\AP;

use App\Models\Note;
use App\Models\NoteAttachment;
use App\Models\NoteMention;
use App\Models\Announcement;
use App\Models\User;
use App\Models\Actor;
use App\Models\Activity;
use App\Models\Instance;
use App\Models\Follow;
use App\Models\Like;
use App\Models\Hashtag;

use App\Types\TypeActor;
use App\Types\TypeActivity;

use App\Types\TypeNote;
use App\Actions\ActionsPost;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\ProfilePin;
use Illuminate\Support\Facades\Storage;

class APOutboxController extends Controller
{
    public function outbox ($name, Request $request)
    {
        $actor = Actor::where ("preferredUsername", $name)->where ("user_id", "!=", null)->first  ();

        // TODO: check we are logged in and we are the logged in user
        switch ($request->get ("type"))
        {
            case "UpdateProfile":
                return $this->handle_update_profile ($actor);
                break;

            case "UpdateNote":
                return $this->handle_update_note ($actor, $request);
                break;

            case "DeleteNote":
                return $this->handle_delete_note ($actor, $request);
                break;

            case "Follow":
                return $this->handle_follow ($actor, $request->get ("object"));
                break;

            case "Unfollow":
                return $this->handle_unfollow ($actor, $request->get ("object"));
                break;

            case "Like":
                return $this->handle_like ($actor, $request->get ("object"));
                break;

            case "Boost":
                return $this->handle_boost ($actor, $request->get ("object"));
                break;

            case "Pin":
                return $this->handle_pin ($actor, $request->get ("object"));
                break;

            case "Post":
                return $this->handle_post ($actor, $request);
                break;

            default:
                Log::info ("APOutboxController@index");
                Log::info (json_encode (request ()->all ()));
                break;
        }
    }

    public function handle_update_profile (Actor $actor)
    {
        $actor_response = TypeActor::build_response ($actor);

        $update_activity = TypeActivity::craft_update ($actor, $actor_response);
        $response = TypeActivity::post_to_instances ($update_activity, $actor);
        return response ()->json ("success", 200);
    }

    public function handle_update_note (Actor $actor, $request)
    {
        // first check if there are new attachments
        if ($request ["attachments"])
        {
            // TODO: Keep old attachments
            $attachments = NoteAttachment::where ("note_id", $request ["note"])->get ();
            foreach ($attachments as $attachment)
            {
                $processed_url = parse_url ($attachment->url);
                $processed_path = $processed_url["path"];

                $processed_path = str_replace ("/storage", "", $processed_path);
                if (Storage::disk ("public")->exists ($processed_path))
                {
                    Storage::disk ("public")->delete ($processed_path);
                }

                $attachment->delete ();
            }
        }

        $note = Note::where ("id", $request ["note"])->first ();
        if (!$note)
            return response ()->json ([ "error" => "note not found" ], 404);

        $note_actor = $note->get_actor ()->first ();
        if ($actor != $note_actor)
            return response ()->json ([ "error" => "not allowed" ], 403);

        $note->summary = $request ["summary"];
        $note->content = $request ["content"];
        $note->save ();

        if ($request ["attachments"])
        {
            ActionsPost::create_attachments ($note, $request ["attachments"]);
        }

        $note_response = TypeNote::build_response ($note);
        $update_activity = TypeActivity::craft_update ($actor, $note_response);
        $response = TypeActivity::post_to_instances ($update_activity, $actor);

        return response ()->json ("success", 200);
    }

    public function handle_delete_note (Actor $actor, $request)
    {
        $note = Note::where ("id", $request ["note"])->first ();
        if (!$note)
            return response ()->json ([ "error" => "note not found" ], 404);

        $note_actor = $note->get_actor ()->first ();
        if ($actor != $note_actor)
            return response ()->json ([ "error" => "not allowed" ], 403);

        $note->delete ();

        $delete_activity = TypeActivity::craft_delete ($actor, $note->note_id);
        $response = TypeActivity::post_to_instances ($delete_activity, $actor);

        return response ()->json ("success", 200);
    }

    public function handle_follow (Actor $actor, string $object)
    {
        $object_actor = Actor::where ("actor_id", $object)->first ();
        if (!$object_actor)
            return response ()->json ([ "error" => "object not found" ], 404);

        if ($actor->actor_id == $object_actor->actor_id)
            return response ()->json ([ "error" => "cannot follow self" ], 400);

        // check we are not following already
        $following_activity = Activity::where ("actor", $actor->actor_id)
            ->where ("object", '"' . str_replace ("/", "\/", $object_actor->actor_id) . '"')
            ->where ("type", "Follow")
            ->first ();
        if ($following_activity)
            return response ()->json ([ "error" => "already following" ], 400);

        $follow_activity = TypeActivity::craft_follow ($actor, $object_actor);
        $response = TypeActivity::post_activity ($follow_activity, $actor, $object_actor);

        $follow = Follow::create ([
            "activity_id" => $follow_activity->id,
            "actor" => $actor->id,
            "object" => $object_actor->id,
        ]);

        // TODO: Check if it was successfully sent
        /* if (!$response || $response->getStatusCode () < 200 || $response->getStatusCode () >= 300)
            return response ()->json ([ "error" => "failed to post activity" ], 500); */

        return [
            "success" => "followed"
        ];
    }

    public function handle_unfollow (Actor $actor, string $object)
    {
        $object_actor = Actor::where ("actor_id", $object)->first ();
        if (!$object_actor)
            return response ()->json ([ "error" => "object not found" ], 404);

        $follow_activity = Activity::where ("actor", $actor->actor_id)
            ->where ("object", json_encode ($object_actor->actor_id, JSON_UNESCAPED_SLASHES))
            ->where ("type", "Follow")
            ->first ();
        if (!$follow_activity)
            return response ()->json ([ "error" => "no follow activity found. " . $actor->actor_id . " unfollowing " . $object_actor->actor_id ], 404);

        $unfollow_activity = TypeActivity::craft_undo ($follow_activity, $actor);
        $response = TypeActivity::post_activity ($unfollow_activity, $actor, $object_actor);

        // TODO: Check if it was successfully sent
        /* if (!$response || $response->getStatusCode () < 200 || $response->getStatusCode () >= 300)
            return response ()->json ([ "error" => "failed to post activity" ], 500); */

        Log::info ($follow_activity);
        $follow_activity->delete ();
        return [
            "success" => "unfollowed"
        ];
    }

    public function handle_like (Actor $actor, $request)
    {
        $object = Note::where ("note_id", $request)->first ();
        if (!$object)
            return response ()->json ([ "error" => "object not found" ], 404);

        $already_liked = $actor->liked_note ($object);
        if ($already_liked)
        {
            // undo the like
            $like_activity = $already_liked->get_activity ()->first ();
            $undo_activity = TypeActivity::craft_undo ($like_activity, $actor);

            $response = TypeActivity::post_activity ($undo_activity, $actor, $object->get_actor ()->first ());

            $like_exists = Like::where ("note_id", $object->id)
                ->where ("actor_id", $actor->id)
                ->first ();
            if ($like_exists)
                $like_exists->delete ();

            return [
                "success" => "unliked"
            ];
        }

        $like_activity = TypeActivity::craft_like ($actor, $object->note_id);

        $like = Like::create ([
            "note_id" => $object->id,
            "activity_id" => $like_activity->id,
            "actor_id" => $actor->id,
        ]);

        $response = TypeActivity::post_activity ($like_activity, $actor, $object->get_actor ()->first ());

        // TODO: Check if it was successfully sent
        /* if (!$response || $response->getStatusCode () < 200 || $response->getStatusCode () >= 300)
            return response ()->json ([ "error" => "failed to post activity" ], 500); */

        return [
            "success" => "liked"
        ];
    }

    public function handle_boost (Actor $actor, $object)
    {
        $object = Note::where ("note_id", $object)->first ();
        if (!$object)
            return response ()->json ([ "error" => "object not found" ], 404);

        $already_boosted = $actor->boosted_note ($object);
        if ($already_boosted)
        {
            $boost_activity = $already_boosted->activity;
            $undo_activity = TypeActivity::craft_undo ($boost_activity, $actor);

            $response = TypeActivity::post_to_instances ($undo_activity, $actor);

            $boost_exists = Announcement::where ("note_id", $object->id)
                ->where ("actor_id", $actor->id)
                ->first ();
            if ($boost_exists)
                $boost_exists->delete ();

            return [
                "success" => "unboosted"
            ];
        }

        $boost_activity = TypeActivity::craft_announce ($actor, $object->note_id);
        $announcement = Announcement::create ([
            "activity_id" => $boost_activity->id,
            "actor_id" => $actor->id,
            "note_id" => $object->id,
        ]);

        $response = TypeActivity::post_to_instances ($boost_activity, $actor);

        return [
            "success" => "boosted"
        ];
    }

    public function handle_pin (Actor $actor, $object)
    {
        $object = Note::where ("note_id", $object)->first ();
        if (!$object)
            return response ()->json ([ "error" => "object not found" ], 404);

        $already_pinned = $object->is_pinned ($actor);
        if ($already_pinned)
        {
            $pin_activity = $already_pinned->activity;
            $remove_activity = TypeActivity::craft_remove ($actor, $object->note_id, $actor->featured);

            $response = TypeActivity::post_to_instances ($remove_activity, $actor);

            $pin_exists = ProfilePin::where ("note_id", $object->id)
                ->where ("actor_id", $actor->id)
                ->first ();
            if ($pin_exists)
                $pin_exists->delete ();

            return [
                "success" => "unpinned"
            ];
        }

        $pin_activity = TypeActivity::craft_add ($actor, $object->note_id, $actor->featured);
        $pin = ProfilePin::create ([
            "activity_id" => $pin_activity->id,
            "actor_id" => $actor->id,
            "note_id" => $object->id,
        ]);

        $response = TypeActivity::post_to_instances ($pin_activity, $actor);

        return [
            "success" => "pinned"
        ];
    }

    public function handle_post (Actor $actor, $request)
    {
        $note = TypeNote::craft_from_outbox ($actor, $request);

        if (isset ($request ["attachments"]))
        {
            ActionsPost::create_attachments ($note, $request ["attachments"]);
        }

        if (isset ($request ["tags"]))
        {
            foreach ($request ["tags"] as $tag)
            {
                $tag_exists = Hashtag::where ("name", $tag ["name"])->first ();
                if ($tag_exists)
                {
                    $note->get_hashtags ()->attach ($tag_exists->id);
                    continue;
                }

                $tag = Hashtag::create ([
                    "name" => $tag ["name"]
                ]);
                $note->get_hashtags ()->attach ($tag->id);
            }
        }

        $mentions = [];
        if (isset ($request ["mentions"]))
        {
            foreach ($request ["mentions"] as $mention)
            {
                $mention_exists = NoteMention::where ("note_id", $note->id)->where ("actor_id", $mention ["href"])->first ();
                if ($mention_exists)
                    continue;

                $object = TypeActor::actor_exists ($mention ["href"]);
                if (!$object)
                    // we don't obtain actors when we are just mentioning them
                    continue;

                $mention = NoteMention::create ([
                    "note_id" => $note->id,
                    "actor_id" => $object->id
                ]);

                $mentions[] = $object->actor_id;
            }
        }

        if ($request ["visibility"] == "public")
        {
            $note->to = [
                "https://www.w3.org/ns/activitystreams#Public"
            ];
            $note->cc = [
                $actor->followers
            ];
        }
        else if ($request ["visibility"] == "followers")
        {
            // TODO: Boosting should be disabled
            $note->to = [
                $actor->followers
            ];
            $note->cc = [];
        }
        else if ($request ["visibility"] == "private")
        {
            // TODO: Boosting should be disabled
            $note->to = $mentions;
        }

        $note->visibility = $request ["visibility"];

        // if the parent note is not public, responses shouldn't be either
        if ($request ["inReplyTo"])
        {
            $parent_note = TypeNote::note_exists($request ["inReplyTo"]);
            if ($parent_note)
            {
                $note->to = $parent_note->to;
                $note->cc = $parent_note->cc;
                $note->visibility = $parent_note->visibility;
            }
        }
        $note->save ();

        $create_activity = TypeActivity::craft_create ($actor, $note);

        $create_activity->to = $note->to;
        $create_activity->cc = $note->cc;

        $note->activity_id = $create_activity->id;
        $note->save ();

        $response = TypeActivity::post_to_instances ($create_activity, $actor, true);
        return response ()->json ("success", 200);
    }
}
