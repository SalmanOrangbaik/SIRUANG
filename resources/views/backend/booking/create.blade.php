@extends('layouts.backend')
@section('content')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-8 grid-margin stretch-card w-100">
                <div class="card">
                    <div class="card-body performane-indicator-card">
                        <div class="d-sm-flex">
                            <h4 class="card-title flex-shrink-1">Tambah Booking</h4>
                        </div>
                        <div class="container">
                            <div class="row justify-content-center">
                                <div class="col-md-15">
                                    <div class="card">
                                        <div class="card-body">
                                            <form action="{{ route('backend.booking.store') }}" method="POST">
                                                @csrf
                                                @if (session('error'))
                                                <div class="alert alert-danger">
                                                    {{ session('error') }}
                                                </div>
                                            @endif
                                                <div class="form-group mb-3 mt-3">
                                                    <label>Ruangan</label>
                                                    <select name="ruang_id" class="form-control @error('ruang_id') is-invalid @enderror">
                                                        <option value="">-- Pilih Ruangan --</option>
                                                        @foreach ($ruangs as $ruang)
                                                            <option value="{{ $ruang->id }}" {{ old('ruang_id') == $ruang->id ? 'selected' : '' }}>
                                                                {{ $ruang->nama }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('ruang_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>


                                                <div class="form-group mb-3 mt-3">
                                                    <label>Tanggal</label>
                                                    <input type="date" name="tanggal" value="{{ old('tanggal') }}"
                                                        class="form-control @error('tanggal') is-invalid @enderror">
                                                    @error('tanggal')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="form-group mb-3 mt-3">
                                                    <label>Jam Mulai</label>
                                                    <input type="time" name="jam_mulai" value="{{ old('jam_mulai') }}"
                                                        class="form-control @error('jam_mulai') is-invalid @enderror">
                                                    @error('jam_mulai')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="form-group mb-3 mt-3">
                                                    <label>Jam Selesai</label>
                                                    <input type="time" name="jam_selesai" value="{{ old('jam_selesai') }}"
                                                        class="form-control @error('jam_selesai') is-invalid @enderror">
                                                    @error('jam_selesai')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="form-group mb-3 mt-3">
                                                    <label>Keterangan</label>
                                                    <input type="text" name="keterangan" value="{{ old('keterangan') }}"
                                                        class="form-control @error('keterangan') is-invalid @enderror">
                                                    @error('keterangan')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="form-group mb-3 mt-3">
                                                    <label>Jumlah Orang</label>
                                                    <input type="number" name="jumlah_orang" value="{{ old('jumlah_orang') }}"
                                                        class="form-control @error('jumlah_orang') is-invalid @enderror">
                                                    @error('jumlah_orang')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <button type="submit" class="btn btn-primary mb-3 mt-2">Simpan</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
