@extends('layout.main')
@section('content')
<div class="h-screen d-block overflow-auto container-card d-flex flex-column" style="background-color: white;">
    <div class="w-100 py-5 px-3">
        <a href="/antrean" style="color: black;">
            <h5 class="d-flex align-items-center" style="gap: 15px;">
                <i class="fa-solid fa-arrow-left"></i>
                Request Jam Antrean
            </h5>
        </a>
    </div>
    <form action="/action/ubahJamSidang/{{ $dataAntrean->id }}" method="POST" class="w-100 px-3 flex-grow-1 d-flex flex-column">
        @csrf
        @method('PUT')
        <div class="w-100 h-auto">
            <div class="mb-4">
                <label for="inputState">Slot Jam Tersedia</label>
                <select id="inputState" name="slotJamTersedia" class="form-input w-100">
                    <option selected>Pilih Jam</option>
                    @forelse($availableSlots as $slot)
                        <option value="{{ $slot }}">{{ $slot }}</option>
                    @empty
                        <option disabled>Tidak ada slot tersedia</option>
                    @endforelse
                </select>
            </div>
        </div>
        <div class="py-3 w-100 flex-grow-1 d-flex align-items-end py-3">
            <button type="submit" class="btn btn-buat-antrean w-100 py-3">Konfirmasi</button>
        </div>
    </form>
</div>
@endsection