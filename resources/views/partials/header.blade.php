<header class="main-header">
    <nav>
        <div class="top">

            <div class="left">
                <a href="{{ route('home') }}">
                    OurSpace
                </a> |
                <a href="{{ route('home') }}">
                    Home
                </a>
            </div>

            <div class="center">
                <form action="{{ route('search') }}" method="get">
                    <label>OurSpace</label>
                    <input type="text" placeholder="Search OurSpace" name="query">
                    <input type="submit" value="Search" class="submit-btn">
                </form>
            </div>

            <div class="right">
                <ul class="topnav signup">
                    <a href="#">Help</a> |
                    @auth
                        <a href="{{ route('logout') }}">Logout</a>
                    @else
                        <a href="{{ route('login') }}">Login</a> |
                        <a href="{{ route('signup') }}">Signup</a>
                    @endauth
                </ul>
            </div>

        </div>

        <ul class="links">
            <li>
                <a href="{{ route('home') }}">&nbsp;Home </a>
            </li>

            <li>
                <a href="#">&nbsp;Browse </a>
            </li>

            <li>
                <a href="#">&nbsp;Search </a>
            </li>

            <li>
                <a href="#">&nbsp;Blog </a>
            </li>

            <li>
                <a href="#">&nbsp;Bulletins </a>
            </li>

            <li>
                <a href="#">&nbsp;Forum </a>
            </li>

            <li>
                <a href="#">&nbsp;Groups </a>
            </li>

            <li>
                <a href="#">&nbsp;Favs </a>
            </li>

            <li>
                <a href="https://github.com/0xd011f4ce/OurSpace">&nbsp;Source </a>
            </li>

            <li>
                <a href="#">&nbsp;Help </a>
            </li>

            <li>
                <a href="#">&nbsp;About</a>
            </li>
        </ul>
    </nav>
</header>
