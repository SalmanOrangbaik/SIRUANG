@extends('layouts.frontend')

@section('content')
    <section class="bg-light">
        <div class="container pb-5">
            <div class="row">
                <div class="col-lg-5 mt-5">
                    <div class="card mb-3">
                        @if ($ruang->cover)
                            <img src="{{ asset('storage/' . $ruang->cover) }}" class="img-fluid rounded" alt="Foto Ruangan">
                        @else
                            <img src="https://via.placeholder.com/500x300?text=No+Image" class="img-fluid rounded"
                                alt="Foto Ruangan">
                        @endif
                    </div>
                </div>
                <!-- col end -->
                <div class="col-lg-7 mt-5">
                    <div class="card">
                        <div class="card-body">
                            <h1 class="h2"><b>{{ $ruang->nama }}</b></h1>

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <ul class="list-inline mt-3">
                                <br>
                                <li class="list-inline-item">
                                    <h6>Kapasitas:</h6>
                                </li>
                                <li class="list-inline-item">
                                    <p class="text-muted"><strong>{{ $ruang->kapasitas }} orang</strong></p>
                                </li>
                                <li class="list-inline item">
                                    <h6>Fasilitas:</h6>
                                </li>

                                <li class="mt-2 ps-3">
                                    @foreach (explode(',', $ruang->fasilitas) as $fasilitas)
                                <li class="mb-1">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    {{ trim($fasilitas) }}
                                </li>
                                @endforeach
                                </li>
                            </ul>

                            <div class="row pt-4">
                                <div class="col d-grid">
                                    <a href="{{ route('booking_ruangan') }}" class="btn btn-secondary">Kembali</a>
                                </div>
                                <div class="col d-grid">
                                    <button type="button" class="btn btn-primary" onclick="toggleBookingForm()">
                                        Booking Sekarang
                                    </button>
                                </div>
                                <div id="bookingForm" class="mt-4" style="display: none;">
                                <form action="{{ route('booking.store') }}" method="POST">
                                    @csrf

                                    <input type="hidden" name="ruang_id" value="{{ $ruang->id }}">

                                    <div class="mb-3">
                                        <label class="form-label">Kapasitas Maksimal</label>
                                        <input type="text" class="form-control" value="{{ $ruang->kapasitas }}" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Tanggal</label>
                                        <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal') }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Jam Mulai</label>
                                        <input type="time" name="jam_mulai" class="form-control" value="{{ old('jam_mulai') }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Jam Selesai</label>
                                        <input type="time" name="jam_selesai" class="form-control" value="{{ old('jam_selesai') }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Keterangan</label>
                                        <input type="text" name="keterangan" class="form-control" maxlength="255"
                                            value="{{ old('keterangan') }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Jumlah Orang</label>
                                        <input type="number" name="jumlah_orang" class="form-control" min="1"
                                            value="{{ old('jumlah_orang') }}" required>
                                    </div>

                                    <button type="submit" class="btn btn-success">
                                        Konfirmasi Booking
                                    </button>
                                </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script>
    function toggleBookingForm() {
        const form = document.getElementById('bookingForm');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }
</script>

@endsection
