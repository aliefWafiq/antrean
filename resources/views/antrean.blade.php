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
        <div class="alert alert-danger mt-2 position-absolute d-none" role="alert" id="gagalAmbilAntrean" style="z-index: 50;">
            Antrean telah diambil, tidak dapat mengambil antrean lagi
        </div>
        @if ($dataPerkara)
        <div class="w-100 h-100 px-3 mt-4">
            <div class="col-12 p-0" style="color: #01421A;">
                <div class="d-flex">
                    <div>
                        <img src="{{ asset('img/logo.png') }}" alt="ambil antrean" class="header-logo-antrean">
                    </div>
                    <div class="py-2">
                        <h5 class="mx-3">SIAGA</h5>
                    </div>
                </div>
                <div class="mt-4">
                    <h4>Selamat datang</h4>
                    <h4>{{ $dataPerkara->noPerkara }}</h4>
                </div>
            </div>
            <!-- <div class="d-flex col-12 p-0 mt-4">
                <a href="/ubahJamSidang" class="button-antrean p-4">
                    <div class="h-50">
                        <img src="{{ asset('img/icon.svg - 2025-09-20T191441.386 1.png') }}" alt="jam sidang">
                    </div>
                    <h6 class="mt-4">Request <br> Jam Sidang</h6>
                </a>
            </div> -->
            <div class="card-antrean py-4 w-100 h-auto" style="background-color: #FFFFFF; border-radius: 16px;">
                <div class="px-4">
                    <!-- @if ($dataAntrean)
                    <div class="d-flex">
                        <p>Antrean Sekarang : </p>
                        <p class="mx-2" id="antreanSekarang"></p>
                    </div>
                    @endif -->
                    <div>
                        <span class="text-gray mt-4">Tanggal Sidang</span>
                        <p class="main-text">{{ \Carbon\Carbon::parse($dataPerkara->tanggal_sidang)->translatedFormat('l, d F Y') }}</p>
                    </div>
                    <div class="mt-4">
                        <span class="text-gray">Lokasi</span>
                        <p class="main-text pr-5">Jl. Lembaga, No. 01, Desa Senggoro, Kecamatan Bengkalis, Kabupaten Bengkalis, Riau</p>
                    </div>
                    <div class="mt-4">
                        @if ($dataAntrean)
                        <p class="main-text"><span class="text-gray">Perkiraan dipanggil pada pukul </span>{{ \Carbon\Carbon::parse ($dataAntrean->jam_perkiraan)->format('H:i') }} WIB</p>
                        @endif
                    </div>
                </div>
                <div class="ticket-divider">
                    <div class="circle-left"></div>
                    <div class="dashed-line"></div>
                    <div class="circle-right"></div>
                </div>
                <div class="px-4 mt-5">
                    <div>
                        <h5>Tiket Antrean Kamu</h5>
                        @if ($dataAntrean)
                        <h1 class="nomor-antrean">{{ $dataAntrean->tiketAntrean }}</h1>
                        @endif
                    </div>
                    @if ($dataAntrean)
                    <div class="d-flex mt-3">
                        <div class="col-6">
                            <span class="text-gray">Sisa Antrean</span>
                            <h4>{{ $countAntreanHariIni }}</h4>
                        </div>
                        <div class="col-6">
                            <span class="text-gray">Peserta Dilayani</span>
                            <h4 id="antreanSekarang"></h4>
                        </div>
                    </div>
                    @endif
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
            <div class="py-3 w-100 flex-grow-1 d-flex align-items-end">
                <a href="/requestJamSidang" class="btn btn-request-jam w-100 py-3">Request Jadwal Sidang</a>
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

@if(session('antreanTelahDiAmbil'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let suksesAmbilAntrean = document.getElementById('gagalAmbilAntrean')
        suksesAmbilAntrean.classList.remove('d-none')
        suksesAmbilAntrean.classList.add('alert-slide-down');
    })
</script>
@endif

@if ($dataAntrean && $dataAntrean->status === 'menunggu')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function updateAntrean(nomorAntrean) {
            const antreanSekarang = document.getElementById('antreanSekarang')

            antreanSekarang.innerText = nomorAntrean.tiketAntrean
        }

        const antreanId = parseInt("{{ $dataAntrean->id }}");;

        window.Echo.channel(`antrean.${antreanId}`).listen('QueueCalled', (event) => {
            window.location.href = '/';
        });

        window.Echo.channel('antrean-display-channel').listen('UpdateDisplayAntrean', (event) => {
            updateAntrean(event.dataAntreanTerkini);
        })
    });
</script>
@endif
@endpush