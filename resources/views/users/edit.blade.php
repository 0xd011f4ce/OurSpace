@extends('partials.layout')

@section('title', 'Edit profile')

@section('content')
<div class="row edit-profile">
    <div class="col w-20 left"></div>

    <div class="col right">
        <h1>Edit profile</h1>
        <p>All fields are optional and can be left empty</p>
        <a href="{{ route ('users.show', [ 'user_name' => $user->name ]) }}">« View Profile</a>

        <div class="profile-pic">
            <h1>{{ $user->name }}</h1>
            <br>
            <img src="{{ $user->avatar }}" alt="{{ $user->name }}" width="180" height="auto">
            <br>
        </div>

        <hr>

        <h1>Profile Picture & Song:</h1>
        <br>
        <form action="{{ route ('users.edit') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <small>Select photo:</small>
            <input type="file" name="avatar" accept="image/*"><br>
            @error("avatar")
                <p class="error">{{ $message }}</p>
            @enderror
            <small>Max file size: 4MB (jpg/png/gif)</small>
            <br><br>
            <small>Select song:</small>
            <input type="file" name="song" accept="audio/*"><br>
            @error("song")
                <p class="error">{{ $message }}</p>
            @enderror
            <small>Max file size: 4MB</small>
            <br><br>
            <small>Select custom notification sound:</small>
            <input type="file" name="notification_sound" accept="audio/*"><br>
            @error("notification_sound")
                <p class="error">{{ $message }}</p>
            @enderror
            <small>Max file size: 1MB</small>
            <h1>Bio:</h1>
            <br>
            <textarea name="bio" id="bio" cols="58" placeholder="Bio">{{ $user->bio }}</textarea>
            @error("bio")
                <p class="error">{{ $message }}</p>
            @enderror
            <br>
            <small>max limit: 256 characters</small>
            <br>
            <br>
            <label for="about_you">About you:</label>
            <input type="text" name="about_you" id="about_you" value="{{ $user->about_you ? $user->about_you : '' }}">
            @error("about_you")
                <p class="error">{{ $message }}</p>
            @enderror
            <br>
            <br>
            <label for="status">Status:</label>
            <input type="text" name="status" id="status" value="{{ $user->status ? $user->status : '' }}">
            @error("status")
                <p class="error">{{ $message }}</p>
            @enderror
            <br>
            <br>
            <label for="mood">Mood:</label>
            <input type="text" name="mood" id="mood" value="{{ $user->mood ? $user->mood : '' }}">
            <h1>Interests:</h1>
            <br>
            <label for="general">General:</label>
            <input type="text" name="general" id="general" value="{{ $user->interests_general ? $user->interests_general : '' }}">
            @error("general")
                <p class="error">{{ $message }}</p>
            @enderror
            <br>
            <br>
            <label for="music">Music:</label>
            <input type="text" name="music" id="" value="{{ $user->interests_music ? $user->interests_music : '' }}">
            @error("music")
                <p class="error">{{ $message }}</p>
            @enderror
            <br>
            <br>
            <label for="movies">Movies:</label>
            <input type="text" name="movies" id="movies" value="{{ $user->interests_movies ? $user->interests_movies : '' }}">
            @error("movies")
                <p class="error">{{ $message }}</p>
            @enderror
            <br>
            <br>
            <label for="television">Television:</label>
            <input type="text" name="television" id="television" value="{{ $user->interests_television ? $user->interests_television : '' }}">
            @error("television")
                <p class="error">{{ $message }}</p>
            @enderror
            <br>
            <br>
            <label for="books">Books:</label>
            <input type="text" name="books" id="books" value="{{ $user->interests_books ? $user->interests_books : '' }}">
            @error("books")
                <p class="error">{{ $message }}</p>
            @enderror
            <br>
            <br>
            <label for="heroes">Heroes:</label>
            <input type="text" name="heroes" id="heroes" value="{{ $user->interests_heroes ? $user->interests_heroes : '' }}">
            @error("heroes")
                <p class="error">{{ $message }}</p>
            @enderror
            <br>
            <h1>Layout:</h1>
            <small>
                what you would normally paste into the 'Blurbs' section. Include HTML tags.
            </small>
            <br>
            <textarea name="blurbs" id="layout" cols="58" placeholder="Layout">{{ $user->blurbs ? $user->blurbs : '' }}</textarea>
            <br>
            <input type="submit" value="Save">
        </form>
    </div>
</div>
@endsection
