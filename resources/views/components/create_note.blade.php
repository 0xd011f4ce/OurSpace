<form action="{{ route ('user.post.new') }}" method="POST" enctype="multipart/form-data">
    @csrf

    @if (isset ($inreplyto))
        <input type="hidden" name="inReplyTo" value="{{ $inreplyto->note_id }}">
    @endif

    <input type="text" name="summary" placeholder="Title" style="width: 100%">

    <br>

    <textarea name="content" placeholder="What's on your mind?" style="width: 100%"></textarea>
    <input type="file" name="files[]" accept="image/*" multiple><br>
    <button type="submit">Post</button>
    <small>Markdown is supported</small>

    @error ("content")
        <div class="error">{{ $message }}</div>
    @enderror

    @error ("files.*")
        <div class="error">{{ $message }}</div>
    @enderror
</form>
