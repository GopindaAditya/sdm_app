<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Pegawai</title>
</head>
<body>
    <nav>
        <a href="{{ route('dashboard') }}">Dashboard</a> | 
        <a href="{{ route('pegawai.profil') }}">Profil</a> | 
        <a href="{{ route('pegawai.kompetensi') }}">Data Kompetensi</a>
        
        <form action="{{ route('logout') }}" method="POST" style="display:inline;">
            @csrf <button type="submit">Logout</button>
        </form>
    </nav>
    <h1 style="color: green;">Selamat Datang di Dashboard Pegawai</h1>
    <p>Halo, {{ Auth::guard('pegawai')->user()->nama }}! Anda login dengan NIP: {{ Auth::guard('pegawai')->user()->nip }}.</p>

    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" style="background: red; color: white;">Logout</button>
    </form>
</body>
</html>