<!DOCTYPE html>
<html>
<head>
    <title>Data Kompetensi</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        .filter-container { margin-bottom: 20px; padding: 10px; border: 1px solid #ccc; background-color: #f9f9f9; }
        .filter-item { margin-right: 15px; display: inline-block; margin-bottom: 10px; }
        nav { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <nav>
        <a href="{{ route('dashboard') }}">Dashboard</a> | 
        <a href="{{ route('profil') }}">Profil</a> | 
        <a href="{{ route('kompetensi') }}">Data Kompetensi</a>
        
        <form action="{{ route('logout') }}" method="POST" style="display:inline; float: right;">
            @csrf <button type="submit">Logout</button>
        </form>
    </nav>

    <h2>Kompetensi yang Harus Dimiliki</h2>

    <div class="filter-container">
        <form id="filterForm" action="{{ route('kompetensi') }}" method="GET">
            
            <div class="filter-item">
                <label for="periode_id">Periode:</label>
                <select name="periode_id" id="periode_id" class="auto-submit">
                    <option value="">-- Pilih Periode --</option>
                    @foreach ($semuaPeriode as $periode)
                        <option value="{{ $periode->id }}" {{ $selectedPeriodeId == $periode->id ? 'selected' : '' }}>
                            {{ $periode->tahun }} ({{ $periode->status }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-item">
                <label for="kategori">Kategori:</label>
                <select name="kategori" id="kategori" class="auto-submit">
                    <option value="">Semua Kategori</option>
                    @foreach ($kategoriList as $kat)
                        <option value="{{ $kat }}" {{ $selectedKategori == $kat ? 'selected' : '' }}>
                            {{ $kat }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-item">
                <label for="status">Status Kepemilikan:</label>
                <select name="status" id="status" class="auto-submit">
                    <option value="">Semua Status</option>
                    <option value="dimiliki" {{ $selectedStatus == 'dimiliki' ? 'selected' : '' }}>Sudah Dimiliki</option>
                    <option value="belum_dimiliki" {{ $selectedStatus == 'belum_dimiliki' ? 'selected' : '' }}>Belum Dimiliki</option>
                </select>
            </div>

            <div class="filter-item">
                <label for="search">Cari:</label>
                <input type="text" name="search" id="search" value="{{ $search }}" placeholder="Nama Kompetensi...">
            </div>

            <button type="submit">Terapkan</button>
            <a href="{{ route('kompetensi') }}"><button type="button">Reset</button></a>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Kompetensi</th>
                <th>Kategori</th>
                <th>Status Kepemilikan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($syaratKompetensi as $index => $syarat)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $syarat->kompetensi->nama_kompetensi }}</td>
                    <td>{{ $syarat->kompetensi->kategori }}</td>
                    <td>
                        @if (in_array($syarat->id_kompetensi, $kompetensiDimilikiIds))
                            <span style="color: green; font-weight: bold;">✔ Sudah Dimiliki</span>
                        @else
                            <span style="color: red; font-weight: bold;">✖ Belum Dimiliki</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" align="center">Tidak ada data kompetensi yang sesuai dengan pencarian atau jabatan Anda pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        $(document).ready(function() {
            // Submit form secara otomatis saat dropdown berubah
            $('.auto-submit').on('change', function() {
                $('#filterForm').submit();
            });
        });
    </script>
</body>
</html>