@extends ("partials.layout")

@section('title', 'Home')

@section('content')
    @auth
        @include ('home_loggedin')
    @else
        <div class="row home">
            <div class="col w-60 left">
                <div class="new-people cool">
                    <div class="top">
                        <h4>Cool new people</h4>
                    </div>
                    <div class="inner">
                        @foreach ($latest_users as $user)
                            <x-user_block :user="$user" />
                        @endforeach
                    </div>
                </div>

                <div class="music">
                    <div class="heading">
                        <h4>OurSpace Music</h4>
                        <a href="#" class="more">[more music]</a>
                    </div>
                    <div class="inner">
                        music goes here
                    </div>
                </div>

                <div class="specials">
                    <div class="heading">
                        <h4>OurSpace Announcements</h4>
                    </div>
                    <div class="inner">
                        announcements go here
                    </div>
                </div>
            </div>

            <div class="col right">
                <div class="welcome">
                    <p>Did you know...? OurSpace is free software and decentralized!</p>
                </div>

                <div class="box">
                    <h4>Member Login/Signup</h4>
                    <form action="{{ route('login') }}" method="POST">
                        @csrf
                        <table>
                            <tbody>

                                <tr class="email">
                                    <td class="label">
                                        <label for="Email">Email:</label>
                                    </td>
                                    <td class="input">
                                        <input type="email" name="email" id="Email" required>
                                    </td>
                                </tr>

                                <tr class="password">
                                    <td class="label">
                                        <label for="Password">Password:</label>
                                    </td>
                                    <td class="input">
                                        <input type="password" name="password" id="Password" required>
                                    </td>
                                </tr>

                                <tr class="remember">
                                    <td></td>
                                    <td>
                                        <input type="checkbox" id="checkbox" name="remember" value="yes">
                                        <label for="checkbox">Remember me</label>
                                    </td>
                                </tr>

                                <tr class="buttons">
                                    <td></td>
                                    <td>
                                        <button class="login_btn" type="submit" name="action" value="login">Login</button>
                                        <button class="signup_btn" type="button" onclick="location.href='/auth/signup'" name="action" value="signup">Sign Up</button>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </form>
                    <a href="#" class="forgot">Forgot your password?</a>
                </div>

                <div class="indie-box">
                    <p>OurSpace is an open source social network. Check out the code and host your own instance. It is also based in the activitypub protocol, so you can reach millions of people.</p>
                    <p>
                        <a href="https://github.com/0xd011f4ce/OurSpace" class="more-details">[more details]</a>
                    </p>
                </div>
            </div>
        </div>

        <div class="row info-area">
            <div class="col info-box">
                <h3>Retro Social</h3>
                <p>All the things you missed most about Social Networks are back: Bulletins, Blogs, Forums, and so much more!</p>
                <p class="link">
                    »<a href="{{ route ('signup') }}">Join Today</a>
                </p>
            </div>

            <div class="col info-box">
                <h3>Privacy Friendly</h3>
                <p>No algorithms, no tracking, no personalized ads - just a safe space for you and your friends to hang out online!</p>
                <p class="link">
                    »<a href="#">Browse Profiles</a>
                </p>
            </div>

            <div class="col info-box">
                <h3>Fully Customizable</h3>
                <p>Featuring custom HTML and CSS to give you all the freedom you need to make your profile truly <i>your Space</i> on the web!</p>
                <p class="link">
                    »<a href="#">Discover Layouts</a>
                </p>
            </div>

            <div class="col info-box">
                <h3>Decentralized and Open source</h3>
                <p>OurSpace is free software and decentralized, so you can host your own instance and reach millions of people through the activitypub protocol!</p>
            </div>
        </div>
    @endauth
@endsection
