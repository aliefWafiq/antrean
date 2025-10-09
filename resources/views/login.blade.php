@extends('layout.main')
@section('content')
<div class="h-screen d-block overflow-auto container-card d-flex flex-column" style="background-color: white;">
    <div class="w-100 py-5 px-3">
        <a href="/" style="color: black;">
            <h5 class="d-flex align-items-center" style="gap: 15px;">
                Login
            </h5>
        </a>
    </div>
    <form action="/login/action" method="POST" class="w-100 px-3 flex-grow-1 d-flex flex-column">
        @csrf
        <div class="w-100 h-auto">
            <div class="mb-4">
                <label for="noHp" class="form-label">NIK atau Nomor HP</label>
                <input type="numbertext" class="form-input w-100" id="noHp" name="noHp" placeholder="Masukkan NIK atau Nomor HP" required>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-input w-100" id="password" name="password" placeholder="Masukkan password" required>
            </div>
        </div>
        <div class="py-3 w-100 flex-grow-1 d-flex align-items-end py-3">
            <button type="submit" class="btn btn-buat-antrean w-100 py-3">Login</button>
        </div>
    </form>
</div>
@endsection