@extends('layouts.frontend')

@section('content')
    <div class="container-fluid contact bg-light py-5">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-lg-6 wow fadeInLeft" data-wow-delay="0.1s">
                    <h1 class="display-4 mb-4">Booking Ruangan</h1>
                </div>

                <div class="col-lg-6 wow fadeInRight" data-wow-delay="0.3s">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('booking.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-floating">
                                    <select name="ruang_id" class="form-select" required>
                                        <option disabled {{ old('ruang_id', $ruang_id ?? '') == '' ? 'selected' : '' }}>
                                            Pilih Ruangan</option>
                                        @foreach ($ruang as $data)
                                            <option value="{{ $data->id }}"
                                                data-kapasitas="{{ $data->kapasitas }}"
                                                {{ old('ruang_id', $ruang_id ?? '') == $data->id ? 'selected' : '' }}>
                                                {{ $data->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label for="ruang_id">Ruangan</label>
                                </div>
                                <div class="small text-muted mt-1" id="ruangKapasitasInfo">Kapasitas maksimal: -</div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="time" class="form-control" name="jam_mulai" required>
                                    <label for="jam_mulai">Jam Mulai</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="time" class="form-control" name="jam_selesai" required>
                                    <label for="jam_selesai">Jam Selesai</label>
                                </div>
                            </div>


                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="date" class="form-control" name="tanggal" required>
                                    <label for="tanggal">Tanggal</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="keterangan" maxlength="255"
                                        value="{{ old('keterangan') }}" required>
                                    <label for="keterangan">Keterangan Booking</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="number" class="form-control" name="jumlah_orang" min="1"
                                        value="{{ old('jumlah_orang') }}" required>
                                    <label for="jumlah_orang">Jumlah Orang</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100 py-3">Booking Sekarang</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('sweetalert::alert')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ruangSelect = document.querySelector('select[name="ruang_id"]');
            const info = document.getElementById('ruangKapasitasInfo');

            function updateInfo() {
                const selected = ruangSelect.options[ruangSelect.selectedIndex];
                const kapasitas = selected ? selected.getAttribute('data-kapasitas') : null;
                info.textContent = kapasitas ? `Kapasitas maksimal: ${kapasitas}` : 'Kapasitas maksimal: -';
            }

            updateInfo();
            ruangSelect.addEventListener('change', updateInfo);
        });
    </script>
@endsection
