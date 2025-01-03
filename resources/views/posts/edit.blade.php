@extends ("partials.layout")

@section ("title", "Edit Post")

@php
    use League\HTMLToMarkdown\HtmlConverter;
    $converter = new HtmlConverter ();
    $markdown = $converter->convert ($note->content);
@endphp

@section ("content")
<div class="row edit-blog-entry">
    <div class="col w-20 left">
        <div class="edit-info">
            <p>Edit your post</p>
        </div>
    </div>

    <div class="col right">
        <h1>Edit Post</h1>
        <p>
            <a href="{{ route ('posts.show', ['note' => $note ]) }}">&larr; View post</a>
        </p>
        <br>

        <form method="POST" enctype="multipart/form-data">
            @csrf
            <input type="text" name="summary" placeholder="Summary" value="{{ old ('summary', $note->summary) }}">
            <br>
            <textarea name="content" id="content">{{ old ('content', $markdown) }}</textarea>
            <br>
            <input type="file" name="files[]" accept="image/*" multiple>

            <div class="publish">
                <button type="submit" name="submit">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
