<form action="{{ route ('user.post.new') }}" method="POST" enctype="multipart/form-data">
    @csrf

    @if (isset ($inreplyto))
        <input type="hidden" name="inReplyTo" value="{{ $inreplyto->note_id }}">
    @endif

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
