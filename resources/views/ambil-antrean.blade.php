@extends('layout.main')
@section('content')
<div class="h-screen d-block overflow-auto container-card d-flex flex-column" style="background-color: white;">
    <div class="w-100 py-5 px-3">
        <a href="/" style="color: black;">
            <h5 class="d-flex align-items-center" style="gap: 15px;">
                <i class="fa-solid fa-arrow-left"></i>
                Ambil Antrean
            </h5>
        </a>
    </div>
    <form action="/store/buatAntrean" method="POST" class="w-100 px-3 flex-grow-1 d-flex flex-column">
        @csrf
        <div class="w-100 h-auto">
            <div class="mb-4">
                <label for="nama_pihak" class="form-label">Nama Pihak</label>
                <input type="text" class="form-input w-100" id="nama_pihak" name="nama_pihak" placeholder="Masukkan nama pihak" required>
            </div>
            <div class="mb-4">
                <label for="nomor_perkara" class="form-label">Nomor Perkara</label>
                <input type="text" class="form-input w-100" id="nomor_perkara" name="nomor_perkara" placeholder="Masukkan nomor perkara" required>
            </div>
            <div class="mb-4">
                <label for="inputState">Jenis Perkara</label>
                <select id="inputState" name="jenis_perkara" class="form-input w-100">
                    <option selected>Pilih jenis perkara</option>
                    <option value="gugatan">Gugatan</option>
                    <option value="permohonan">Permohonan</option>
                </select>
            </div>
        </div>
        <div class="py-3 w-100 flex-grow-1 d-flex align-items-end py-3">
            <button type="submit" class="btn btn-buat-antrean w-100 py-3">Buat Antrean</button>
        </div>
    </form>
</div>
@endsection