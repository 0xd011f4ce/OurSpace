@extends("partials.layout")

@section ("title", "View Post")

@section ("content")
<div class="row article blog-entry">
    <div class="col w-20 left">
        <div class="edit-info">
            <div class="profile-pic">
                <img loading="lazy" src="{{ $actor->user ? $actor->user->avatar : $actor->icon }}" class="pfp-fallback">
            </div>

            <div class="author-details">
                <h4>
                    Published by
                    <span>
                        <a href="{{ route ('users.show', [ 'user_name' => $actor->user ? $actor->user->name : $actor->local_actor_id ]) }}">
                            {{ $actor->name }}
                        </a>
                    </span>
                </h4>

                <p class="publish-date">
                    published <time class="ago">
                        {{ $note->created_at->diffForHumans() }}
                    </time>
                    <br>
                </p>

                <p class="links">
                    <a href="{{ route ('users.show', [ 'user_name' => $actor->user ? $actor->user->name : $actor->local_actor_id ]) }}">
                        <img loading="lazy" src="/resources/icons/user.png" class="icon">
                        <span class="m-hide">View</span> Profile
                    </a>
                </p>
            </div>
        </div>
    </div>

    <div class="col right">
        <h1 class="title">{{ $actor->name }}'s Post</h1>
        @if (auth ()->check () && auth ()->user ()->is ($actor->user))
            <form action="#" method="POST">
                @csrf
                @method("DELETE")
                <a href="{{ route ('posts.edit', [ 'note' => $note ]) }}">
                    <button type="button">Edit</button>
                </a>
                <button type="submit">Delete</button>
            </form>
        @endif
        <div class="content">
            <div class="heading">
                <h4>{{ $note->summary }}</h4>
            </div>
            {!! $note->content !!}

            @foreach ($note->attachments as $attachment)
                <img loading="lazy" src="{{ $attachment->url }}" width="250">
            @endforeach
        </div>

        <br>

        <div class="buttons">
            <form action="{{ route ('posts.like', [ 'note' => $note->id ]) }}" method="POST">
                @csrf
                <button type="submit">{{ auth ()->user ()->actor ()->first ()->liked_note ($note) ? "Undo Like" : "Like" }}</button>
            </form>
        </div>

        <p>
            <b>Likes</b>: {{ $note->get_likes ()->count () }}
        </p>

        <div class="comments" id="comments">
            <div class="heading">
                <h4>Comments</h4>
            </div>

            <div class="inner">
                <p>
                    <b>Displaying <span class="count">0</span> of <span class="count">0</span> comments</b>
                </p>

                <table class="comments-table" cellspacing="0" cellpadding="3" bordercolor="ffffff" border="1">
                    <tbody>
                        <!-- TODO: Comments -->
                        Comments go here
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
