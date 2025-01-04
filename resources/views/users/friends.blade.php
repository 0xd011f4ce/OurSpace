@extends ("partials.layout")

@section ("title", $user->name . "'s Friends")

@section ("content")
<div class="simple-container">
    <h1>{{ $user->name }}'s Friends</h1>
    <p>
        <a href="{{ route ('users.show', [ 'user_name' => $user->name ]) }}">&laquo; Back to profile</a>
    </p>

    <br>

    <form>
        <input type="text" name="query" value="{{ request ()->get ('query') ?? '' }}">
        <button type="submit">Search</button>
    </form>

    <br>

    <div class="new-people">
        <div class="top">
            <h4>Friends</h4>
        </div>
        <div class="inner">
            @forelse ($friends as $friend)
                <x-user_block :user="$friend" />
            @empty
                <p>No friends found.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
