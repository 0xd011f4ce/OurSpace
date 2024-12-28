@extends ("partials.layout")

@section('title', "$user->name's Profile")

@section('content')
    <div class="row profile">

        <div class="col w-40 left">
            <span>
                <h1>{{ $user->name }}</h1>
            </span>

            <div class="general-about">

                <div class="profile-pic">
                    <img loading="lazy" src="{{ $user->avatar }}" alt="{{ $user->name }}'s pfp" class="pfp-fa" style="width: 235px; height: auto">
                </div>

                <div class="details">
                    <p>{{ $user->status }}</p>
                    <p>{{ $user->about_you }}</p>
                    <p class="online">
                        <img loading="lazy" src="/resources/img/green_person.png" alt="online"> ONLINE!
                    </p>
                </div>

            </div>

            <audio src="#" id="music" autoplay loop controls></audio>

            <div class="mood">
                <p><b>Mood:</b> {{ $user->mood }}</p>
                <p><b>View my: <a href="#">Blog</a> | <a href="#">Bulletins</a></b></p>
            </div>

            <div class="contact">
                <div class="heading">
                    <h4>Contacting {{ $user->name }}</h4>
                </div>

                <div class="inner">
                    <div class="f-row">
                        <div class="f-col">
                            <a href="#">
                                <img loading="lazy" src="/resources/icons/add.png" alt=""> Add to Friends
                            </a>
                        </div>

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
            </div>

            <div class="url-info">
                <p>
                    <b>Federation handle:</b>
                </p>
                <p>@php echo "@" . $user->name . "@" . explode ("/", env ("APP_URL"))[2] @endphp</p>
            </div>

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
        </div>

        <div class="col right">
            @auth
                @if (auth()->user()->is($user))
                    <div class="profile-info">
                        <h3>
                            <a href="{{ route ('users.edit') }}">Edit Your Profile</a>
                        </h3>
                    </div>
                @endif
            @endauth

            <div class="blog-preview">
                <h4>
                    {{ $user->name }}'s Latest Blog Entries [<a href="#">View Blog</a>]
                </h4>
                <p>
                    <i>There are no Blog Entries yet.</i>
                </p>
            </div>

            <div class="blurbs">
                <div class="heading">
                    <h4>
                        {{ $user->name }}'s Bio
                    </h4>
                </div>
                <div class="inner">
                    <div class="section">
                        <p>{{ $user->bio }}</p>
                    </div>
                </div>
            </div>

            <div class="friends">
                <div class="heading">
                    <h4>
                        {{ $user->name }}'s Friend Space
                    </h4>
                    <a href="#" class="more">[view all]</a>
                </div>

                <div class="inner">

                    <p>
                        <b>
                            {{ $user->name }} has <span class="count">{{ count ($user->mutual_friends ()) }}</span> friends.
                        </b>
                    </p>

                    <div class="friends-grid"></div>

                </div>
            </div>

            <div id="comments" class="friends">
                <div class="heading">
                    <h4>{{ $user->name }}'s Friends Comments</h4>
                </div>
                <div class="inner">
                    <p>
                        <b>
                            Displaying <span class="count">0</span> of <span class="count">0</span> comments (<a href="#">View all</a> | <a href="#">Add Comment</a>)
                        </b>
                    </p>

                    <table class="comments-table" cellspacing="0" cellpadding="3" bordercollor="#ffffff" border="1">
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection
