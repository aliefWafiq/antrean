@extends('layout.main')
@section('content')
<div class="d-flex flex-column vh-100 text-center container-card" style="background-color: white;">
    <div class="flex-grow-1 d-flex flex-column justify-content-between w-100">
        <div class="h-50 d-flex align-items-center">
            <img src="{{ asset('img/logo.png') }}" alt="Logo Pengadilan Agama" class="logo mx-auto">
        </div>
        <div class="w-100">
            <p class="mb-md-4 heading-welcome" style="color: #01421A; font-weight: 700;">SIAGA APA</p>
            <p class="mb-md-4 subheading-welcome" style="color: #01421A; font-weight: 600;">
                Selamat Datang di 
                Pengadilan Agama 
                Kabupaten Bengkalis
            </p>
            <img src="{{ asset('img/IMG_0104 1.png') }}" alt="Gedung Pengadilan Agama Bengkalis" class="img-fluid rounded w-100">
        </div>
    </div>

    <div class="py-3 px-3">
        <a href="/login" class="btn btn-buat-antrean w-100 py-3">
            Login
        </a>
    </div>
</div>
@endsection