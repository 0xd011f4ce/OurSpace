<tr>
    <td>
        <a href="{{ route ('users.show', [ 'user_name' => $actor->local_actor_id ]) }}">
            <p>
                <b>{{ $actor->name }}</b>
            </p>
        </a>
        <a href="{{ route ('users.show', [ 'user_name' => $actor->local_actor_id ]) }}">
            <p>
                @if ($actor->user)
                    <img loading="lazy" src="{{ $actor->user->avatar }}" class="pfp-fallback" width="50">
                @else
                    <img loading="lazy" src="{{ $actor->icon }}" class="pfp-fallback" width="50">
                @endif
            </p>
        </a>
    </td>
    <td>
        <p>
            <b>
                <time>{{ Carbon\Carbon::parse ($post->created_at)->diffForHumans () }}</time>
            </b>
        </p>
        <p>
            {!! $post->content !!}
        </p>
        <p>
            @foreach ($post->attachments as $attachment)
                <img loading="lazy" src="{{ $attachment->url }}" alt="{{ $attachment->name }}" width="100">
            @endforeach
        </p>
    </td>
</tr>
