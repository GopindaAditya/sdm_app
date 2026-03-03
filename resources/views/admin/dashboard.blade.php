<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Admin</title>
</head>
<body>
    <h1 style="color: blue;">Selamat Datang di Dashboard Admin</h1>
    <p>Halo, {{ Auth::guard('admin')->user()->username }}! Anda login sebagai Administrator.</p>

    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" style="background: red; color: white;">Logout</button>
    </form>
</body>
</html>