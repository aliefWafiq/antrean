@extends('layout.main')
@section('content')
<div class="h-screen d-block overflow-auto container-card d-flex flex-column justify-content-between" style="background-color: #E7F9CF;">
    <div class="w-100 h-auto d-flex flex-column align-items-center" style="z-index: 20;">
        <div class="w-100" style="z-index: 50;">
            <div class="d-flex flex-column align-items-start h-25 px-3 py-4">
                <img src="{{ asset('img/logo.png') }}" alt="logo">
                <h4 class="fw-bold mt-4">Selamat Datang di Pengadilan Agama Kabupaten Bengkalis</h4>
            </div>
        </div>
        <div class="w-100 d-flex justify-content-center" style="position: absolute; z-index: 0;">
            <img src="{{ asset('img/--background.png') }}" alt="background" class="img-fluid object-fit-cover bg-image">
        </div>
        <div class="w-100 h-100 px-3">
            @if (session('antrean'))
            <div class="card-antrean py-4 w-100 h-auto mt-2" style="background-color: #FFFFFF; border-radius: 16px;">
                <div class="px-4">
                    <h5>Tiket Antrean Kamu</h5>
                    <h1>{{ session('antrean')->tiketAntrean }}</h1>
                </div>
                <div class="ticket-divider">
                    <div class="circle-left"></div>
                    <div class="dashed-line"></div>
                    <div class="circle-right"></div>
                </div>
                <div class="px-4 mt-5">
                    <div>
                        <span class="text-gray">Tanggal Sidang</span>
                        <p class="main-text">{{ session('antrean')->tanggal_sidang }}</p>
                    </div>
                    <div class="mt-4">
                        <span class="text-gray">Lokasi</span>
                        <p class="main-text pr-5">Jl. Lembaga, No. 01, Desa Senggoro, Kecamatan Bengkalis, Kabupaten Bengkalis, Riau</p>
                    </div>
                    <div class="mt-4">
                        <p class="main-text"><span class="text-gray">Perkiraan dipanggil pada pukul </span>{{ session('antrean')->jam_perkiraan }} WIB</p>
                    </div>
                    <div style="background-color: #E2E6FF; border-radius: 8px;" class="mt-4">
                        <p class="main-text p-3">Mohon hadir 15 menit sebelum waktu sidang</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    <div class="py-3 w-100 px-3">
        <a href="/ambil-antrean" class="btn btn-buat-antrean w-100 py-3">Buat Antrean</a>
    </div>
    <div class="modal fade modal-bottom" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog"> <!-- Removed modal-dialog-centered -->
            <div class="modal-content">
                <div class="modal-body text-center">
                    <img src="{{ asset('img/popup-sucess.png') }}" alt="Success Icon" width="120" class="mb-4">
                    <h4 class="fw-bold mb-3">Buat Antrean Berhasil</h4>
                    <p class="text-muted mb-4">
                        Nomor Antrean Kamu berhasil dibuat, silahkan lihat e-tiket di halaman beranda dan tunggu di ruang sidang untuk menunggu antrean.
                    </p>
                    <button type="button" class="btn btn-success-custom" data-bs-dismiss="modal">Kembali ke Beranda</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
@if (session('showModal'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();
    });
</script>
@endif
@endpush