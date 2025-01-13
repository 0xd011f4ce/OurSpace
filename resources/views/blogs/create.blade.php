@extends ("partials.layout")

@section ("title", "Create a new blog")

@section ("content")
    <div class="row edit-blog-entry">
        <div class="col w-20 left">
            <div class="edit-info">
                <p>You can use markdown in the description of your blog!</p>
            </div>
        </div>

        <div class="col right">
            <h1>Create Blog</h1>
            <br>

            <form method="POST" class="ctrl-enter-submit" enctype="multipart/form-data">
                @csrf

                <label for="name">Name:</label>
                <input type="text" name="name" id="name" value="{{ old ('name') }}" required>
                @error("name")
                    <p class="error">{{ $message }}</p>
                @enderror

                <label for="description">Description:</label>
                <textarea name="description" id="description">{{ old ("description") }}</textarea>
                @error("description")
                    <p class="error">{{ $message }}</p>
                @enderror

                <label for="icon">Logo:</label>
                <input type="file" name="icon" id="icon" accept="image/*" required>
                @error("icon")
                    <p class="error">{{ $message }}</p>
                @enderror

                <br>
                <label for="category">Category:</label>
                <select name="category" id="category">
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>

                <div class="publish">
                    <button type="submit" name="submit">Create Blog!</button>
                </div>
            </form>
        </div>
    </div>
@endsection
