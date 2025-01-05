<div class="person">
    <a href="{{ route ('users.show', [ 'user_name' => $user->local_actor_id ? $user->local_actor_id : $user->name ]) }}">
        <p>{{ $user->name }}</p>
    </a>
    <a href="{{ route ('users.show', [ 'user_name' => $user->local_actor_id ? $user->local_actor_id : $user->name ]) }}">
        @if ($user instanceof App\Models\User)
            <img loading="lazy" src="{{ $user->avatar }}" alt="{{ $user->name }}'s profile picture"
                    class="pfp-fallback" style="width: 100%; max-height: 95px; aspect-ratio: 1/1">
        @else
            <img loading="lazy" src="{{ $user->user_id ? $user->user ()->first ()->avatar : $user->icon }}" alt="{{ $user->name }}'s profile picture"
                class="pfp-fallback" style="width: 100%; max-height: 95px; aspect-ratio: 1/1">
        @endif
    </a>
</div>
