@extends ("partials.layout")

@section ("title", "Notifications")

@section ("content")
    <div class="simple-container">
        <h1>Notifications</h1>
        <p>You have <b>{{ count ($user->unreadNotifications) }}</b> unread notifications</p>
        <br>

        <table border="1" width="100%">
            <tr>
                <th style="width: 100px">Actor</th>
                <th>Content</th>
                <th>Time</th>
                <th>Read</th>
            </tr>

            @foreach ($processed_notifications as $notification)
                <tr @if ($notification ['read_at'] == null) style="font-weight: bold" @endif>
                    <td>
                        <a href="{{ route ('users.show', [ 'user_name' => $notification ['actor']->local_actor_id ? $notification ['actor']->local_actor_id : $notification ['actor']->name ]) }}">
                            <p>{{ $notification ['actor']->name }}</p>
                        </a>
                    </td>

                    <td>
                        @if ($notification ['type'] == 'Follow')
                            <p>Followed you</p>
                        @elseif ($notification ['type'] == 'Unfollow')
                            <p>Unfollowed you</p>
                        @elseif ($notification ['type'] == 'Boost')
                            <p>Boosted this <b><a href="{{ route ('posts.show', ['note' => $notification['object']->id]) }}">post</a></b></p>
                        @elseif ($notification ['type'] == 'Like')
                            <p>Liked this <b><a href="{{ route ('posts.show', ['note' => $notification['object']->id]) }}">post</a></b></p>
                        @elseif ($notification ['type'] == 'Reply')
                            <p>Replied to this <b><a href="{{ route ('posts.show', ['note' => $notification['object']->id]) }}">post</a></b></p>
                        @elseif ($notification ['type'] == 'Mention')
                            <p>Mentioned you in this <b><a href="{{ route ('posts.show', ['note' => $notification['object']->id])}}">post</a></b></p>
                        @endif
                    </td>

                    <td>
                        <p>{{ $notification ['created_at']->diffForHumans () }}</p>
                    </td>

                    <td>
                        <input type="checkbox" @if ($notification ['read_at'] != null) checked @endif disabled>
                    </td>
                </tr>
            @endforeach
        </table>

        {{ $notifications->links ("pagination::default") }}
    </div>
@endsection
