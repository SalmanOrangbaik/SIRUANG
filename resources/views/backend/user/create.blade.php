@extends('layouts.backend')
@section('content')
    <div class="main-panel">
        <div class="content-wrapper">
            <div class="row">
                <div class="col-md-8 grid-margin stretch-card w-100">
                    <div class="card">
                        <div class="card-body performane-indicator-card">
                            <div class="d-sm-flex">
                                <h4 class="card-title flex-shrink-1">Data User</h4>
                            </div>
                            <div class="container">
                                <div class="row justify-content-center">
                                    <div class="col-md-15">
                                        <div class="card">

                                            <div class="card-body">
                                                <form action="{{ route('backend.user.store') }}" method="POST"
                                                    enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="form-group mb-5 mt-3">
                                                        <label>Nama </label>
                                                        <input type="text"
                                                            class="form-control @error('name') is-invalid @enderror"
                                                            name="name">
                                                        @error('name')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="form-group mb-5 mt-3">
                                                        <label>Email</label>
                                                        <input type="email" name="email"
                                                            class="form-control @error('email') is-invalid @enderror">
                                                        @error('email')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="form-group mb-5 mt-3">
                                                        <label>Password</label>
                                                        <input type="password" name="password"
                                                            class="form-control @error('password') is-invalid @enderror">
                                                        @error('password')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="form-group mb-5 mt-3">
                                                        <label>Konfirmasi Password</label>
                                                        <input type="password" name="password_confirmation"
                                                            class="form-control @error('password_confirmation') is-invalid @enderror">
                                                        @error('password_confirmation')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="form-group mb-5 mt-3">
                                                        <label>Role</label>
                                                        <select name="role"
                                                            class="form-control @error('role') is-invalid @enderror">
                                                            <option value="0"
                                                                {{ old('role') == '0' ? 'selected' : '' }}>User</option>
                                                            <option value="1"
                                                                {{ old('role') == '1' ? 'selected' : '' }}>Admin</option>
                                                        </select>
                                                        @error('role')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="form-group mb-5 mt-3">
                                                        <label>Status</label>
                                                        <select name="status" class="form-control @error('status') is-invalid @enderror">
                                                            <option value="" disabled {{ old('status') ? '' : 'selected' }}>-- Pilih Status --</option>
                                                            <option value="Siswa" {{ old('status') == 'Siswa' ? 'selected' : '' }}>Siswa</option>
                                                            <option value="Guru" {{ old('status') == 'Guru' ? 'selected' : '' }}>Guru</option>
                                                        </select>
                                                        @error('status')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="form-group mb-5 mt-3">
                                                        <label>NISN (untuk Siswa)</label>
                                                        <input type="text" name="nisn"
                                                            class="form-control @error('nisn') is-invalid @enderror"
                                                            value="{{ old('nisn') }}">
                                                        @error('nisn')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="form-group mb-5 mt-3">
                                                        <label>NIP (untuk Guru)</label>
                                                        <input type="text" name="nip"
                                                            class="form-control @error('nip') is-invalid @enderror"
                                                            value="{{ old('nip') }}">
                                                        @error('nip')
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
            @endsection
