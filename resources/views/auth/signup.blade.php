@extends ("partials.layout")

@section ("title", "Login")

@section ("content")

<h1>Sign Up</h1>

<div class="center-container">
    <div class="contactInfo">
        <div class="contactInfoTop">
            <center>Benefits</center>
        </div>
        - Make new friends!<br>
        - Talk to people!<br>
        - Algorithm free!<br>
        - Free and open source!<br>
        - Embrace decentralization!
    </div>

    <br><br>

    <form action="#" method="POST">
        @csrf
        <input type="text" name="name" placeholder="Username" required><br>
        @error('username')
            <div class="error">{{ $message }}</div>
        @enderror

        <input type="email" name="email" placeholder="Email" required><br>
        @error('email')
            <div class="error">{{ $message }}</div>
        @enderror

        <input type="password" name="password" placeholder="Password" required><br>
        @error('password')
            <div class="error">{{ $message }}</div>
        @enderror

        <input type="password" name="password_confirmation" placeholder="Confirm Password" required><br><br>
        <button type="submit">Sign Up</button>
    </form>
</div>
@endsection
