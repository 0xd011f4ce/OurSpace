<div class="row profile user-home">
    <div class="col w-40 left">
        <div class="general-about home-actions">
            <div class="heading">
                <h1>Hello, {{ auth()->user()->name }}</h1>
            </div>

            <div class="inner">
                <br>
                <div class="profile-pic">
                    <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}">
                </div>

                <div class="details">
                    <p>
                        <a href="{{ route('users.edit') }}">Edit profile</a>
                    </p>
                    <p>
                        <a href="#">Edit status</a>
                    </p>
                </div>

                <div class="more-options">
                    <p>
                        View My
                        <a href="{{ route('users.show', ['user_name' => auth()->user()->name]) }}">Profile</a>
                        |
                        <a href="#">Blog</a>
                        |
                        <a href="#">Bulletins</a>
                        |
                        <a href="#">Friends</a>
                    </p>
                    <p>
                        My URL:
                        <a
                            href="{{ route('users.show', ['user_name' => auth()->user()->name]) }}">{{ route('users.show', ['user_name' => auth()->user()->name]) }}</a>
                    </p>
                </div>
            </div>
        </div>

        <div class="url-info view-full-profile">
            <p>
                <a href="{{ route('users.show', ['user_name' => auth()->user()->name]) }}"><b>View Your Profile</b></a>
            </p>
        </div>

        <div class="indie-box">
            <a href="https://github.com/0xd011f4ce/OurSpace">OurSpace is an open source social network. Check out the
                code and host your own instance. It is also based in the activitypub protocol, so you can reach millions
                of people.</a>
        </div>
    </div>

    <div class="col right">
        <div class="col right">
            <div class="row top-row">
                <div class="blog-preview col">
                    <h4>Your Latest Blog Entries [<a href="#">New Entry</a>]</h4>
                    <p>
                        <i>There are no Blog Entries yet.</i>
                    </p>
                </div>

                <div class="statistics col">
                    <div class="heading">
                        <h4>{{ auth ()->user ()->name }}'s Statistics</h4>
                        <br>
                        <h4>
                            {{ date ('F j, Y') }}
                        </h4>
                    </div>

                    <div class="inner">
                        <div class="m-row">
                            <div class="m-col">
                                <p>
                                    Your Friends:
                                    <br>
                                    <span class="count">{{ count (auth ()->user ()->mutual_friends ()) }}</span>
                                </p>
                            </div>

                            <div class="m-col">
                                <p>
                                    Joined:
                                    <br>
                                    <span class="count">
                                        <i>{{ auth ()->user ()->created_at->diffForHumans () }}</i>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="new-people cool">
                <div class="top">
                    <h4>Cool New People</h4>
                    <a href="#" class="more">[view more]</a>
                </div>

                <div class="inner">
                    @foreach ($latest_users as $user)
                        <x-user_block :user="$user" />
                    @endforeach
                </div>
            </div>

            <div class="friends">
                <div class="heading">
                    <h4>Friend Requests</h4>
                </div>
                <div class="inner">
                    <p>
                        <b>
                            <span class="count">{{ count (auth ()->user ()->received_requests ()) }}</span>
                            Open Friend Requests
                        </b>
                    </p>
                    <a href="{{ route ('requests') }}">
                        <button>
                            View All Requests
                        </button>
                    </a>
                </div>
            </div>

            <div class="bulletin-preview">
                <div class="heading">
                    <h4>Feed</h4>
                </div>

                <div class="inner">
                    <x-create_note /><Br>

                    <table class="comments-table" cellspacing="0" cellpadding="3" bordercollor="#ffffff" border="1">
                        <tbody>
                            @foreach (auth ()->user ()->feed () as $post)
                                <x-comment_block :actor="$post->get_actor ()->first ()" :post="$post" />
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
