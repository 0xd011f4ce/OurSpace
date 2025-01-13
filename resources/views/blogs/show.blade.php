@extends ("partials.layout")

@section ("title", $blog->name)

@section ("content")
    <div class="row profile">
        <div class="col w-30 left">
            <h1>
                {{ $blog->name }}
            </h1>

            <div class="general-about">
                <div class="profile-pic">
                    <img loading="lazy" src="{{ $blog->actor->icon }}" alt="{{ $blog->name }}" class="pfp-fallback">
                </div>

                <div class="details below">
                    @if ($blog->user->is_online ())
                        <p class="online">
                            <img loading="lazy" src="/resources/img/green_person.png"> ONLINE!
                        </p>
                    @endif
                </div>
            </div>

            <div class="mood">
                <p><b>Mood:</b> {{ $blog->user->mood }}</p>
                <br>
                <p>
                    <b>View my: <a href="{{ route ('users.show', [ 'user_name' => $blog->user->name ]) }}">Profile</a></b>
                </p>
            </div>

            <div class="url-info">
                <p>
                    <b>
                        Federation Handle:
                    </b>
                </p>
                <p>@php echo "@" . $blog->slug . "@" . explode ("/", env ("APP_URL"))[2] @endphp</p>
            </div>

            <div class="url-info view-full-profile">
                <p>
                    <a href="{{ route ('users.show', [ 'user_name' => $blog->user->name ]) }}">
                        <b>View Full Profile</b>
                    </a>
                </p>
            </div>
        </div>

        <div class="col right">
            <div class="blog-preview">
                <h1>
                    {{ $blog->name }}'s Blog Entries
                </h1>

                <div class="blog-preview">
                    <h3>
                        [
                            <a href="{{ route ('blogs.new_entry', [ 'blog' => $blog->slug ]) }}">
                                New Entry
                            </a>
                        ]
                    </h3>
                </div>

                <br>

                <div class="blog-entries">
                    <h3>Pinned</h3>

                    @foreach ($blog->pinned_notes as $note)
                        <x-blog_entry_block :note="$note->note" />
                    @endforeach

                    <hr>

                    @foreach ($notes as $note)
                        <x-blog_entry_block :note="$note" />
                    @endforeach

                    {{ $notes->links () }}
                </div>
            </div>
        </div>
    </div>
@endsection
