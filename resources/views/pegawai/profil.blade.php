<!DOCTYPE html>
<html>
<head><title>Profil Pegawai</title></head>
<body>
    <nav>
        <a href="{{ route('dashboard') }}">Dashboard</a> | 
        <a href="{{ route('pegawai.profil') }}">Profil</a> | 
        <a href="{{ route('pegawai.kompetensi') }}">Data Kompetensi</a>
        
        <form action="{{ route('logout') }}" method="POST" style="display:inline;">
            @csrf <button type="submit">Logout</button>
        </form>
    </nav>

    <h2>Profil Pegawai</h2>
    <ul>
        <li><strong>NIP:</strong> {{ $pegawai->nip }}</li>
        <li><strong>Nama:</strong> {{ $pegawai->nama }}</li>
        <li><strong>Jabatan:</strong> {{ $pegawai->jabatan->nama_jabatan ?? 'Belum memiliki jabatan' }}</li>
    </ul>
</body>
</html>