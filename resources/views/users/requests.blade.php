@extends ("partials.layout")

@section ("title", "Open Friend Requests")

@section ("content")
    <div class="simple-container">

        <div class="friends">
            <div class="heading">
                <h1>Friend Requests</h1>
            </div>

            <div class="inner">
                <br>
                <p>
                    <b>
                        <span class="count">{{ count ($user->received_requests ()) }}</span>
                        Open Friend Requests
                    </b>

                    <form action="#" method="POST">
                        @csrf
                        <button type="submit" name="submit">Accept All Requests</button>
                    </form>
                    <br>

                    <table class="comments-table" cellspacing="0" cellpadding="3" bordercolor="ffffff" border="1">
                        <tbody>
                            @foreach ($received_requests as $frequest)
                                <tr>
                                    <td>
                                        <a href="{{ route ('users.show', [ 'user_name' => $frequest->local_actor_id ? $frequest->local_actor_id : $frequest->preferredUsername ]) }}">
                                            <p>
                                                {{ $frequest->name ? $frequest->name : $frequest->preferredUsername }}
                                            </p>
                                        </a>

                                        <a href="{{ route ('users.show', [ 'user_name' => $frequest->local_actor_id ? $frequest->local_actor_id : $frequest->preferredUsername ]) }}">
                                            <img src="{{ $frequest->user ? $frequest->user->avatar : $frequest->icon }}" alt="{{ $frequest->name }}" class="avatar">
                                        </a>
                                    </td>
                                    <td>
                                        <p>
                                            <b>Friend Request</b>
                                        </p>
                                        <form method="POST">
                                            @csrf
                                            <input type="hidden" name="accept" value="{{ $frequest->actor_id }}">
                                            <input type="submit" name="submit" value="Accept">
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </p>

                <br>
                <p>
                    <b>
                        <span class="count">{{ count ($user->sent_requests ()) }}</span>
                        Pending Friend Requests
                    </b>
                    <br>

                    <table class="comments-table" cellspacing="0" cellpadding="3" bordercolor="ffffff" border="1">
                        <tbody>
                            @foreach ($sent_requests as $frequest)
                            <tr>
                                <td>
                                    <a href="{{ route ('users.show', [ 'user_name' => $frequest->local_actor_id ? $frequest->local_actor_id : $frequest->preferredUsername ]) }}">
                                        <p>
                                            {{ $frequest->name }}
                                        </p>
                                    </a>

                                    <a href="{{ route ('users.show', [ 'user_name' => $frequest->local_actor_id ? $frequest->local_actor_id : $frequest->preferredUsername ]) }}">
                                        <img src="{{ $frequest->user ? $frequest->user->avatar : $frequest->icon }}" alt="{{ $frequest->name }}" class="avatar">
                                    </a>
                                </td>
                                <td>
                                    <p>
                                        <b>Sent Request</b>
                                    </p>
                                    <form method="POST">
                                        @csrf
                                        <input type="hidden" name="cancel" value="{{ $frequest->actor_id }}">
                                        <input type="submit" name="submit" value="Cancel">
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </p>
            </div>
        </div>

    </div>
@endsection
