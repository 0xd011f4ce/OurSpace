<?php

namespace App\Jobs;

use App\Models\Actor;
use GuzzleHttp\Client;

use App\Models\Activity;

use App\Types\TypeActivity;

use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class PostActivityJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    protected $activity;
    protected $actor;
    protected $target;
    protected $should_sign;

    /**
     * Create a new job instance.
     */
    public function __construct(Activity $activity, Actor $actor, $target, $should_sign = false)
    {
        $this->activity = $activity;
        $this->actor = $actor;
        $this->target = $target;
        $this->should_sign = $should_sign;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $crafted_activity = TypeActivity::craft_response($this->activity);

        if ($this->should_sign)
        {
            $crafted_activity ["to"] = [
                "https://www.w3.org/ns/activitystreams#Public",
            ];

            $crafted_activity ["cc"] = [
                $this->actor->following
            ];

            $key = TypeActivity::get_private_key($this->actor);
            $activity_json = json_encode($crafted_activity, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $signature = TypeActivity::sign($activity_json, $key);

            $crafted_activity ["signature"] = [
                "type" => "RsaSignature2017",
                "creator" => $this->actor->actor_id . "#main-key",
                "created" => gmdate("Y-m-d\TH:i:s\Z"),
                "signatureValue" => base64_encode($signature)
            ];

            $activity_json = json_encode($crafted_activity, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        $activity_json = json_encode($crafted_activity, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $activity_json = mb_convert_encoding($activity_json, "UTF-8");

        $headers = TypeActivity::craft_signed_headers($activity_json, $this->actor, $this->target);
        if (!$headers)
        {
            throw new \Exception("Failed to craft headers");
        }

        $target_inbox = null;

        if ($this->target instanceof Actor)
        {
            $target_inbox = $this->target->inbox;
        }
        else
        {
            $target_inbox = $this->target;
        }

        $client = new Client ();
        $response = $client->post($target_inbox, [
            "headers" => $headers,
            "body" => $activity_json,
            "debug" => true
        ]);
    }

    public function failed (\Exception $exception)
    {
        Log::error("Failed to post activity: " . $exception->getMessage());
    }
}
