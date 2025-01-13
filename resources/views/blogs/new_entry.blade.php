@extends ("partials.layout")

@section ("title", "Create New Entry")

@section ("content")
    <div class="row edit-blog-entry">
        <div class="col w-20 left">
            <div class="edit-info">
                <p>You can use Markdown in the content of your entry!</p>
            </div>
        </div>

        <div class="col right">
            <h1>New Entry</h1>
            <br>

            <form method="POST" class="ctrl-enter-submit" enctype="multipart/form-data" action="{{ route ('user.post.new') }}">
                @csrf

                <input type="hidden" name="blog_id" value="{{ $blog->id }}">

                <label for="summary">Title:</label>
                <input type="text" name="summary" id="summary" value="{{ old ('summary') }}" placeholder="A really cool post!" required>
                @error("summary")
                    <p class="error">{{ $message }}</p>
                @enderror

                <br>

                <textarea name="content" placeholder="What's on your mind?">{{ old ('content') }}</textarea>
                <small>Markdown is supported</small>
                @error("content")
                    <p class="error">{{ $message }}</p>
                @enderror
                <br>

                <label for="files">Attachments:</label><br>
                <input type="file" name="files[]" accept="image/*" id="files" multiple><br>
                @error("files.*")
                    <p class="error">{{ $message }}</p>
                @enderror
                <br>

                <label for="visibility">Visibility</label>
                <select name="visibility">
                    <option value="public">Public</option>
                    <option value="followers">Friends only</option>
                    <option value="private">Private</option>
                </select>
                <br><br>

                <button type="submit">Post!</button>
            </form>
        </div>
    </div>
@endsection
