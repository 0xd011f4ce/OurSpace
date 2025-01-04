<div class="person">
    <a href="{{ route ('users.show', [ 'user_name' => $user->name ]) }}">
        <p>{{ $user->name }}</p>
    </a>
    <a href="{{ route ('users.show', [ 'user_name' => $user->name ]) }}">
        <img loading="lazy" src="{{ $user->avatar }}" alt="{{ $user->name }}'s profile picture"
            class="pfp-fallback" style="width: 100%; max-height: 95px; aspect-ratio: 1/1">
    </a>
</div>
