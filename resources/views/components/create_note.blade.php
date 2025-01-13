<form action="{{ route ('user.post.new') }}" method="POST" enctype="multipart/form-data">
    @csrf

    @if (isset ($inreplyto))
        <input type="hidden" name="inReplyTo" value="{{ $inreplyto->note_id }}">
    @endif

    <input type="text" name="summary" placeholder="Title" style="width: 100%">

    <br>

    <textarea name="content" placeholder="What's on your mind?" style="width: 100%"></textarea>
    <small>Markdown is supported</small>
    <br>
    <input type="file" name="files[]" accept="image/*" multiple><br>
    <div>
        <b>Visibility:</b>
        <select name="visibility">
            <option value="public">Public</option>
            <option value="followers">Friends only</option>
            <option value="private">Mentioned Only</option>
        </select>
    </div>
    <button type="submit">Post</button>

    @error ("content")
        <div class="error">{{ $message }}</div>
    @enderror

    @error ("files.*")
        <div class="error">{{ $message }}</div>
    @enderror
</form>
