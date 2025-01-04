@extends ("partials.layout")

@section('title', "$actor->name's Profile")

@section('content')
    <div class="row profile">

        <div class="col w-40 left">
            <span>
                <h1>{{ $actor->name }}</h1>
            </span>

            <div class="general-about">

                <div class="profile-pic">
                    @if ($user == null)
                        <img loading="lazy" src="{{ $actor->icon }}" alt="{{ $actor->preferredUsername }}'s pfp" class="pfp-fa" style="width: 235px; height: auto">
                    @else
                        <img loading="lazy" src="{{ $user->avatar }}" alt="{{ $actor->preferredUsername }}'s pfp" class="pfp-fa" style="width: 235px; height: auto">
                    @endif
                </div>

                @if ($user != null)
                    <div class="details">
                        <p>{{ $user->status }}</p>
                        <p>{{ $user->about_you }}</p>
                        @if (auth ()->user () && auth ()->user ()->actor ()->first ()->is ($actor))
                            <p class="online">
                                <img loading="lazy" src="/resources/img/green_person.png" alt="online"> YOU!
                            </p>
                        @else
                            <p class="online">
                                <img loading="lazy" src="/resources/img/green_person.png" alt="online"> ONLINE!
                            </p>
                        @endif
                    </div>
                @endif

            </div>

            <audio src="#" id="music" autoplay loop controls></audio>

            <div class="mood">
                @if ($user != null)
                    <p><b>Mood:</b> {{ $user->mood }}</p>
                    <p><b>View my: <a href="#">Blog</a> | <a href="#">Bulletins</a></b></p>
                @endif
            </div>

            <div class="contact">
                <div class="heading">
                    <h4>Contacting {{ $actor->name }}</h4>
                </div>

                @auth
                <div class="inner">
                    <div class="f-row">
                        @if (!auth ()->user ()->is ($user))
                            <div class="f-col">
                                @if (auth ()->user ()->actor->friends_with ($actor))
                                    <form action="{{ route ('user.unfriend') }}" onclick="this.submit ()" method="post" style="cursor: pointer">
                                        @csrf
                                        <input type="hidden" name="object" value="{{ $actor->actor_id }}">
                                        <img loading="lazy" src="/resources/icons/delete.png" alt=""> Remove Friend
                                    </form>
                                @elseif (in_array ($actor->actor_id, auth ()->user ()->received_requests ()))
                                    <form action="{{ route ('user.friend') }}" onclick="this.submit ()" method="post" style="cursor: pointer">
                                        @csrf
                                        <input type="hidden" name="object" value="{{ $actor->actor_id }}">
                                        <img loading="lazy" src="/resources/icons/add.png" alt=""> Accept Friend Request
                                    </form>
                                @elseif (in_array ($actor->actor_id, auth ()->user ()->sent_requests ()))
                                    <form action="{{ route ('user.unfriend') }}" onclick="this.submit ()" method="post" style="cursor: pointer">
                                        @csrf
                                        <input type="hidden" name="object" value="{{ $actor->actor_id }}">
                                        <img loading="lazy" src="/resources/icons/hourglass.png" alt=""> Cancel Request
                                    </form>
                                @else
                                    <form action="{{ route ('user.friend') }}" onclick="this.submit ()" method="post" style="cursor: pointer">
                                        @csrf
                                        <input type="hidden" name="object" value="{{ $actor->actor_id }}">
                                        <img loading="lazy" src="/resources/icons/add.png" alt=""> Add to Friends
                                    </form>
                                @endif
                            </div>
                        @else
                            <div class="f-col">
                                <a href="{{ route ('users.edit') }}">
                                    <img loading="lazy" src="/resources/icons/asterisk_yellow.png" alt=""> Edit Profile
                                </a>
                            </div>
                        @endif

                        <div class="f-col">
                            <a href="#">
                                <img loading="lazy" src="/resources/icons/award_star_add.png" alt=""> Add to Favorites
                            </a>
                        </div>
                    </div>

                    <div class="f-row">
                        <div class="f-col">
                            <a href="#">
                                <img loading="lazy" class="icon" src="/resources/icons/comment.png" alt=""> Send Message
                            </a>
                        </div>

                        <div class="f-col">
                            <a href="#">
                                <img loading="lazy" class="icon" src="/resources/icons/arrow_right.png" alt=""> Forward to Friend
                            </a>
                        </div>
                    </div>

                    <div class="f-row">
                        <div class="f-col">
                            <a href="#">
                                <img loading="lazy" class="icon" src="/resources/icons/email.png" alt=""> Instant Message
                            </a>
                        </div>

                        <div class="f-col">
                            <a href="#">
                                <img loading="lazy" class="icon" src="/resources/icons/exclamation.png" alt=""> Block User
                            </a>
                        </div>
                    </div>

                    <div class="f-row">
                        <div class="f-col">
                            <a href="#">
                                <img loading="lazy" class="icon" src="/resources/icons/group_add.png" alt=""> Add to Group
                            </a>
                        </div>

                        <div class="f-col">
                            <a href="#">
                                <img loading="lazy" class="icon" src="/resources/icons/flag_red.png" alt=""> Report User
                            </a>
                        </div>
                    </div>
                </div>
                @endauth
            </div>

            <div class="url-info">
                <p>
                    <b>Federation handle:</b>
                </p>
                @if ($user != null)
                    <p>@php echo "@" . $user->name . "@" . explode ("/", env ("APP_URL"))[2] @endphp</p>
                @else
                    <p>{{ $actor->local_actor_id }}</p>
                @endif
            </div>

            @if ($user != null)
                <div class="table-section">
                    <div class="heading">
                        <h4>{{ $user->name }}'s Interests</h4>
                    </div>
                    <div class="inner">
                        <table class="details-table" cellspacing="3" cellpadding="3">
                            <tbody>

                                <tr>
                                    <td>
                                        <p>General</p>
                                    </td>
                                    <td>
                                        <p>{{ $user->interests_general }}</p>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <p>Music</p>
                                    </td>
                                    <td>
                                        <p>{{ $user->interests_music }}</p>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <p>Movies</p>
                                    </td>
                                    <td>
                                        <p>{{ $user->interests_movies }}</p>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <p>Television</p>
                                    </td>
                                    <td>
                                        <p>{{ $user->interests_television }}</p>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <p>Books</p>
                                    </td>
                                    <td>
                                        <p>{{ $user->interests_books }}</p>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <p>Heroes</p>
                                    </td>
                                    <td>
                                        <p>{{ $user->interests_heroes }}</p>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>

        <div class="col right">
            @auth
                @if ($user != null && auth()->user()->is($user))
                    <div class="profile-info">
                        <h3>
                            <a href="{{ route ('users.edit') }}">Edit Your Profile</a>
                        </h3>
                    </div>
                @endif
            @endauth

            @if ($user != null)
            <div class="blog-preview">
                <h4>
                    {{ $user->name }}'s Latest Blog Entries [<a href="#">View Blog</a>]
                </h4>
                <p>
                    <i>There are no Blog Entries yet.</i>
                </p>
            </div>
            @endif

            <div class="blurbs">
                <div class="heading">
                    <h4>
                        {{ $actor->name }}'s Bio
                    </h4>
                </div>
                <div class="inner">
                    <div class="section">
                        <p>{!! $actor->summary !!}</p>
                    </div>
                </div>
            </div>

            @if ($user != null)
                <div class="friends">
                    <div class="heading">
                        <h4>
                            {{ $actor->name }}'s Friend Space
                        </h4>
                        <a href="{{ route ('users.friends', [ 'user_name' => $actor->preferredUsername ]) }}" class="more">[view all]</a>
                    </div>

                    <div class="inner">

                        <p>
                            <b>
                                {{ $actor->name }} has <span class="count">{{ count ($user->mutual_friends ()) }}</span> friends.
                            </b>
                        </p>

                        <div class="friends-grid"></div>

                    </div>
                </div>
            @endif

            <div id="comments" class="friends">
                <div class="heading">
                    <h4>{{ $actor->name }}'s Posts</h4>
                </div>
                <div class="inner">
                    <p>
                        <b>{{ $actor->name }} has <span class="count">{{ count ($actor->posts) }}</span> posts.</b>
                    </p>

                    @if (auth ()->user () && auth ()->user ()->is ($user))
                        <form action="{{ route ('user.post.new') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="text" name="summary" placeholder="Title" size="60">
                            <br>
                            <textarea name="content" placeholder="What's on your mind?" cols="60" rows="5"></textarea>
                            <input type="file" name="files[]" accept="image/*" multiple>
                            <button type="submit">Post</button>
                            <small>Markdown is supported</small>

                            @error ("content")
                                <div class="error">{{ $message }}</div>
                            @enderror

                            @error ("files.*")
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </form>
                    @endif

                    <br>

                    <table class="comments-table" cellspacing="0" cellpadding="3" bordercollor="#ffffff" border="1">
                        <tbody>
                            @foreach ($actor->posts as $post)
                                <x-comment_block :actor="$actor" :post="$post" />
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection
