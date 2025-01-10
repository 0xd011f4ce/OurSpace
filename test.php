use App\Models\Actor;
use App\Models\User;

use App\Events\UserFollowed;

$actor = Actor::find(3);
$user = User::find(1);

UserFollowed::dispatch ($actor, $user);
