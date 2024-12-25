@extends ("partials.layout")

@section ("title", "Login")

@section ("content")
<div class="center-container">
    <div class="box standalone">

        <h4>Please login or signup to continue</h4>
        <form action="#" method="POST">
            @csrf

            <table>
                <tbody>

                    <tr class="email">
                        <td class="label">
                            <label for="email">E-Mail:</label>
                        </td>

                        <td class="input">
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>

                    <tr class="password">
                        <td class="label">
                            <label for="password">Password:</label>
                        </td>

                        <td class="input">
                            <input type="password" name="password" id="password" required>
                            @error('password')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>

                    <tr class="remember">
                        <td></td>
                        <td>
                            <input type="checkbox" name="remember" value="yes" id="checkbox">
                            <label for="checkbox">Remember my E-mail</label>
                        </td>
                    </tr>

                    <tr class="buttons">
                        <td></td>
                        <td>
                            <button type="submit" class="login_btn" name="action" value="login">Login</button>
                            <button type="button" class="signup_btn" onclick="location.href='/auth/signup'" name="action" value="signup">Sign Up</button>
                        </td>
                    </tr>

                </tbody>
            </table>
        </form>

        <a href="#" class="forgot">Forgot your password?</a>

    </div>
</div>
@endsection
