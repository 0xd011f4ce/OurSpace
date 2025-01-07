<ul class="cloud">
    @foreach ($hashtags as $hashtag)
    <li>
        <a href="{{ route ('tags', [ 'tag' => substr ($hashtag->name, 1) ]) }}"
            data-weight="{{ $hashtag->get_notes_count }}">
            {{ $hashtag->name }}
        </a>
    </li>
    @endforeach
</ul>

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
            // set a minimum size
            const min_size = 100;
            const size = min_size + (weight / max_weight) * 100;

            link.style.fontSize = `${size}%`;
        });
    })
</script>
