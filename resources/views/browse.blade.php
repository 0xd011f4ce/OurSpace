@extends ("partials.layout")

@section ("title", "Explore")

@section ("content")
    <div class="simple-container">
        <h1>Browse Users</h1>

        <div class="new-people">
            <div class="top">
                <h4>Active Users</h4>
                <a href="#" class="more">[random]</a>
            </div>

            <div class="inner">
                @foreach ($latest_users as $user)
                    <x-user_block :user="$user" />
                @endforeach
            </div>
        </div>

        <h1>Popular Hashtags</h1>

        <div class="new-people">
            <div class="top">
                <h4>Hashtags</h4>
            </div>

            <div class="inner">
                <ul class="cloud">
                    @foreach ($popular_hashtags as $hashtag)
                    <li>
                        <a href="{{ route ('tags', [ 'tag' => substr ($hashtag->name, 1) ]) }}"
                            data-weight="{{ $hashtag->get_notes_count }}">
                            {{ $hashtag->name }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <h1>Trending Posts</h1>
        <small>The posts with the most likes in the last 24 hours</small>

        <table class="comments-table" cellspacing="0" cellpadding="3" bordercollor="#ffffff" border="1">
            <tbody>
                @foreach ($popular_notes as $post)
                    <x-comment_block :post="$post" />
                @endforeach
            </tbody>
        </table>
    </div>

    <script>
        document.addEventListener ("DOMContentLoaded", () => {
            const links = document.querySelectorAll ("ul.cloud a");
            let max_weight = 0;

            links.forEach ((link) => {
                const weight = parseInt (link.getAttribute ("data-weight"));

                if (weight > max_weight) {
                    max_weight = weight;
                }
            });

            links.forEach ((link) => {
                const weight = parseInt (link.getAttribute ("data-weight"));
                const size = Math.round ((weight / max_weight) * 210);

                link.style.fontSize = `${size}%`;
            });
        })
    </script>
@endsection
