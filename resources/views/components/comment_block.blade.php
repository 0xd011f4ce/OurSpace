@php
$actor_url = "";

if ($actor->user_id)
    $actor_url = route ('users.show', [ 'user_name' => $actor->user->name ]);
else
    $actor_url = route ('users.show', [ 'user_name' => $actor->local_actor_id ]);
@endphp

<tr>
    <td>
        <a href="{{ $actor_url }}">
            <p>
                <b>{{ $actor->name }}</b>
            </p>
        </a>
        <a href="{{ $actor_url }}">
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
                <time>{{ $post->created_at->diffForHumans () }}</time>
            </b>
        </p>

        {!! $post->content !!}

        <p>
            @foreach ($post->attachments as $attachment)
                <img loading="lazy" src="{{ $attachment->url }}" alt="{{ $attachment->name }}" width="100">
            @endforeach
        </p>

        <br>
        <hr>

        <a href="{{ route ('posts.show', [ 'note' => $post ]) }}">
            <button type="button">View</button>
        </a>
        @if ($actor->user && auth ()->check () && auth ()->user ()->is ($actor->user))
            <form action="#" method="POST" style="display: inline">
                @csrf
                <a href="#">
                    <button type="button">
                        Edit
                    </button>
                </a>
                <input type="submit" value="Delete">
            </form>
        @endif
    </td>
</tr>
