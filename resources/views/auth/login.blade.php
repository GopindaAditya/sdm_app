<!DOCTYPE html>
<html>
<head>
    <title>Login Aplikasi SDM</title>
</head>
<body>
    <h2>Login Sistem</h2>

    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ url('/login') }}" method="POST">
        @csrf <div>
            <label>Username / NIP:</label><br>
            <input type="text" name="username_or_nip" value="{{ old('username_or_nip') }}" required autofocus>
        </div>
        <br>
        
        <div>
            <label>Password:</label><br>
            <input type="password" name="password" required>
        </div>
        <br>
        
        <button type="submit">Login</button>
    </form>
</body>
</html>