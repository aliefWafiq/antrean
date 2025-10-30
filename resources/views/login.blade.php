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
            @if (session('error'))
            <div class="alert alert-danger" role="alert">
                Data yang dimasukkan salah
            </div>
            @endif
            <div class="mb-4">
                <label for="NomorPerkara" class="form-label">Nomor Perkara</label>
                <input type="NomorPerkara" class="form-input w-100" id="autoComplete" name="NomorPerkara" placeholder="Masukkan Nomor Perkara" required>
            </div>
            <div class="mb-4">
                <label for="NamaPihak" class="form-label">Nama Pihak</label>
                <input type="NamaPihak" class="form-input w-100" id="NamaPihak" name="NamaPihak" placeholder="Masukkan Nama Pihak" required>
            </div>
            <!-- <div class="mb-4">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-input w-100" id="email" name="email" placeholder="Masukkan Email" required>
            </div> -->
            <!-- <div class="mb-4">
                <label for="noHp" class="form-label">Nomor HP</label>
                <input type="text" class="form-input w-100" id="noHp" name="noHp" placeholder="Masukkan Nomor HP" required>
            </div> -->
            <!-- <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-input w-100" id="password" name="password" placeholder="Masukkan password" required>
            </div> -->
        </div>
        <div class="py-3 w-100 flex-grow-1 d-flex align-items-end py-3">
            <!-- <button type="submit" class="btn btn-buat-antrean w-100 py-3">Login</button> -->
            <button class="btn btn-buat-antrean w-100 py-3">Login</button>
        </div>
    </form>
</div>
@endsection
@push('script')
<script src="https://cdn.jsdelivr.net/npm/@tarekraafat/autocomplete.js@10.2.9/dist/autoComplete.min.js"></script>
<script>
    const autoCompleteNomor = new autoComplete({
        selector: "#autoComplete",
        data: {
            src: async (query) => {
                try {
                    const source = await fetch(`{{ URL('/search/') }}/${query}`);
                    const data = await source.json();
                    return data;
                } catch (error) {
                    return error;
                }
            },
            keys: ["noPerkara"]
        },
        resultsList: {
            element: (list, data) => {
                if (!data.results.length) {
                    const message = document.createElement("div");
                    message.classList.add("no_result");
                    message.innerHTML = `<span>Tidak ada hasil untuk "${data.query}"</span>`;
                    list.prepend(message);
                }
            },
            noResults: true,
        },
        resultItem: {
            highlight: true,
        },
        events: {
            input: {
                selection: (event) => {
                    const selection = event.detail.selection.value;
                    autoCompleteNomor.input.value = selection.noPerkara;
                }
            }
        }
    });
</script>
@endpush