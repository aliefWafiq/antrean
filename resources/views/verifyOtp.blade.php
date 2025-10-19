@extends('layout.main')
@section('content')
<div class="h-screen d-block overflow-auto container-card d-flex flex-column" style="background-color: white;">
    <div class="w-100 py-5 px-3">
        <a href="/" style="color: black;">
            <h5 class="d-flex align-items-center" style="gap: 15px;">
                Verifikasi OTP
            </h5>
        </a>
    </div>
    <form action="/login/verify-otp" method="POST" class="w-100 px-3 flex-grow-1 d-flex flex-column">
        @csrf
        <div class="w-100 h-auto">
            @if (session('error'))
            <div class="alert alert-danger" role="alert">
                Kode OTP salah atau expired, silahkan cek lagi kode yang dikirim
            </div>
            @endif
            @if (session('success'))
            <div class="alert alert-success" role="alert">
                Kode OTP telah di kirim ke Email anda.
            </div>
            @endif
            <div class="mb-4">
                <label for="otp" class="form-label">Kode OTP</label>
                <input type="number"
                    class="form-input w-100 @if (session('error')) input-error @endif"
                    id="otp"
                    name="otp"
                    placeholder="Masukkan kode OTP"
                    required>
            </div>
            <div class="d-flex">
                <p>Kode OTP salah?</p>
                <a href="/kirim-ulang-otp" class="mx-1" style="font-weight: bold;"> Kirim Ulang</a>
            </div>
        </div>
        <div class="py-3 w-100 flex-grow-1 d-flex align-items-end py-3">
            <button type="submit" class="btn btn-buat-antrean w-100 py-3">Login</button>
        </div>
    </form>
</div>
@endsection