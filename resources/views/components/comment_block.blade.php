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

        @if ($post->in_reply_to)
            <small>
                In response to
                <a href="{{ route ('posts.show', [ 'note' => $post->get_parent ()->first ()->id ]) }}">this post</a>
            </small>
        @endif

        <h4>{{ $post->summary }}</h4>

        {!! $post->content !!}

        <p>
            @foreach ($post->attachments as $attachment)
                <img loading="lazy" src="{{ $attachment->url }}" alt="{{ $attachment->name }}" width="100">
            @endforeach
        </p>

        <br>

        @if ($post->get_replies ()->count () > 0)
            <div class="comment-replies">
                @foreach ($post->get_replies ()->get () as $reply)
                    <div class="comment-reply">
                        <h4>{{ $reply->summary }}</h4>

                        {!! $reply->content !!}

                        <p>
                            @foreach ($reply->attachments as $attachment)
                                <img loading="lazy" src="{{ $attachment->url }}" alt="{{ $attachment->name }}" width="100">
                            @endforeach
                        </p>

                        <p>
                            <small>
                                by
                                <a href="{{ route ('users.show', [ 'user_name' => $reply->get_actor ()->first ()->user_id ? $reply->get_actor ()->first ()->user->name : $reply->get_actor ()->first ()->local_actor_id ]) }}">
                                    <b>{{ $reply->get_actor ()->first ()->name }}</b>
                                </a>
                                ;
                                <time class="ago">{{ $reply->created_at->diffForHumans () }}</time>
                            </small>
                        </p>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($post->get_hashtags ()->count () > 0)
            <p>
                <b>Tags:</b>
                @foreach ($post->get_hashtags ()->get () as $hashtag)
                    <a href="{{ route ('tags', [ 'tag' => substr ($hashtag->name, 1) ]) }}">
                        <span class="tag">{{ $hashtag->name }}</span>
                    </a>
                @endforeach
            </p>
        @endif

        <hr>

        <p>
            <b>Likes:</b> {{ $post->get_likes ()->count () }}
        </p>
        <p>
            <b>Replies:</b> {{ $post->get_replies ()->count () }}
        </p>

        <a href="{{ route ('posts.show', [ 'note' => $post ]) }}">
            <button type="button">View</button>
        </a>
        {{-- @if ($actor->user && auth ()->check () && auth ()->user ()->is ($actor->user))
            <form action="#" method="POST" style="display: inline">
                @csrf
                <a href="#">
                    <button type="button">
                        Edit
                    </button>
                </a>
                <input type="submit" value="Delete">
            </form>
        @endif --}}
    </td>
</tr>
