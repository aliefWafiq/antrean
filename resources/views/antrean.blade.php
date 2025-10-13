@extends('layout.main')
@section('content')
<div class="h-screen d-block overflow-auto container-card d-flex flex-column justify-content-between" style="background-color: #E7F9CF;">
    <div class="w-100 h-auto d-flex flex-column align-items-center" style="z-index: 20;">
        <div class="w-100 d-flex justify-content-center" style="position: absolute; z-index: 0;">
            <img src="{{ asset('img/--background.png') }}" alt="background" class="img-fluid object-fit-cover bg-image">
        </div>
        <div class="alert alert-success mt-2 position-absolute d-none" role="alert" id="suksesAmbilAntrean" style="z-index: 50;">
            Antrean telah diambil, silahkan tunggu di ruang tunggu
        </div>
        @if ($dataAntrean)
        <div class="w-100 h-100 px-3 mt-4">
            <div class="col-12 p-0" style="color: #01421A;">
                <div class="d-flex">
                    <div>
                        <img src="{{ asset('img/logo.png') }}" alt="ambil antrean">
                    </div>
                    <div class="py-2">
                        <h5 class="mx-3">SIAGA</h5>
                    </div>
                </div>
                <div class="mt-4">
                    <h4>Selamat datang</h4>
                    <h4>{{ $dataAntrean->namaLengkap }}</h4>
                </div>
            </div>
            <div class="d-flex col-12 p-0 mt-4">
                <a href="/ubahJamSidang" class="button-antrean p-4">
                    <div class="h-50">
                        <img src="{{ asset('img/icon.svg - 2025-09-20T191441.386 1.png') }}" alt="jam sidang">
                    </div>
                    <h6 class="mt-4">Request <br> Jam Sidang</h6>
                </a>
                <form action="/ambil-antrean/{{ $dataAntrean->id }}" method="POST" class="ml-4">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="button-antrean p-4 h-100">
                        <div class="h-50 d-flex align-items-center">
                            <img src="{{ asset('img/Group.png') }}" alt="ambil antrean">
                        </div>
                        <h6 class="mt-4">Ambil <br> Antrean</h6>
                    </button>
                </form>
            </div>
            <div class="card-antrean py-4 w-100 h-auto my-4" style="background-color: #FFFFFF; border-radius: 16px;">
                <div class="px-4">
                    <h5>Tiket Antrean Kamu</h5>
                    <h1>{{ $dataAntrean->tiketAntrean }}</h1>
                </div>
                <div class="ticket-divider">
                    <div class="circle-left"></div>
                    <div class="dashed-line"></div>
                    <div class="circle-right"></div>
                </div>
                <div class="px-4 mt-5">
                    <div>
                        <span class="text-gray">Tanggal Sidang</span>
                        <p class="main-text">{{ \Carbon\Carbon::parse($dataAntrean->tanggal_sidang)->translatedFormat('l, d F Y') }}</p>
                    </div>
                    <div class="mt-4">
                        <span class="text-gray">Lokasi</span>
                        <p class="main-text pr-5">Jl. Lembaga, No. 01, Desa Senggoro, Kecamatan Bengkalis, Kabupaten Bengkalis, Riau</p>
                    </div>
                    <div class="mt-4">
                        <p class="main-text"><span class="text-gray">Perkiraan dipanggil pada pukul </span>{{ \Carbon\Carbon::parse($dataAntrean->jam_perkiraan)->format('H:i') }} WIB</p>
                    </div>
                    <div style="background-color: #E2E6FF; border-radius: 8px;" class="mt-4">
                        <p class="main-text p-3">Mohon hadir 15 menit sebelum waktu sidang</p>
                    </div>
                    <div class="mt-4">
                        <p class="text-gray">
                            Jadwal sidang dalam tampilan ini bersifat tentatif dan dapat berubah sesuai dengan kondisi persidangan, kehadiran para pihak, kelengkapan berkas, serta kebijakan hakim yang memeriksa perkara.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="text-center mt-5">
            <p>Tidak ada data antrean untuk ditampilkan.</p>
        </div>
        @endif
    </div>

    <div class="modal fade modal-bottom" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <img src="{{ asset('img/popup-sucess.png') }}" alt="Success Icon" width="120" class="mb-4">
                    <h4 class="fw-bold mb-3">Ubah Jam Sidang Berhasil</h4>
                    <p class="text-muted mb-4">
                        Nomor Antrean Kamu berhasil dibuat, silahkan lihat e-tiket di halaman beranda dan tunggu di ruang sidang untuk menunggu antrean
                    </p>
                    <button type="button" class="btn btn-success-custom" data-dismiss="modal">Kembali ke Beranda</button>
                </div>
            </div>
        </div>
    </div>
    <div class="btn-logout d-flex justify-content-end px-3">
        <a href="/logout" class="btn btn-danger">
            Log out
        </a>
    </div>
</div>
@endsection

@push('script')
@if (session('showModal'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();
    });
</script>
@endif

@if(session('showSucess'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let suksesAmbilAntrean = document.getElementById('suksesAmbilAntrean')
        suksesAmbilAntrean.classList.remove('d-none')
        suksesAmbilAntrean.classList.add('alert-slide-down');
    })
</script>
@endif

@if ($dataAntrean && $dataAntrean->status === 'menunggu')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const antreanId = parseInt("{{ $dataAntrean->id }}");

        // window.Echo.connector.pusher.connection.bind('state_change', function(states) {
        //     console.log("[KONEKSI] Status koneksi Pusher berubah dari:", states.previous, "ke:", states.current);
        // });

        window.Echo.channel(`antrean.${antreanId}`)
            .listen('QueueCalled', (event) => {
                window.location.href = '/';
            });
    });
</script>
@endif
@endpush