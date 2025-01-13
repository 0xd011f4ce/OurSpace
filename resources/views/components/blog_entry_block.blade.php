<div class="entry">
    <p class="publish-date">
        <time class="ago">
            {{ $note->created_at->diffForHumans () }}
        </time>
    </p>

    <div class="inner">
        <h3 class="title">
            <a href="{{ route ('posts.show', [ 'note' => $note ]) }}">
                {{ $note->summary }}
            </a>
        </h3>
        <p>
            <a href="{{ route ('posts.show', [ 'note' => $note ]) }}">
                &raquo; View Blog Entry
            </a>
        </p>
    </div>
</div>
