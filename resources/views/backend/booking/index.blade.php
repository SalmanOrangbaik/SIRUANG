@extends('layouts.backend')

@section('content')
    <div class="main-panel">
        <div class="content-wrapper">
            <div class="row">
                <div class="col-md-12 grid-margin stretch-card w-100">
                    <div class="card">
                        <div class="card-body performane-indicator-card">
                            <div class="d-sm-flex justify-content-between align-items-center mb-3">
                                <h4 class="card-title">Data Booking</h4>
                                <div>
                                    <a href="{{ route('backend.booking.export', request()->query()) }}" class="btn btn-sm btn-danger me-2">
                                        <i class="fa fa-file-pdf"></i> Export PDF
                                    </a>
                                    <a href="{{ route('backend.booking.create') }}" class="btn btn-sm btn-outline-dark">
                                        Tambah Data
                                    </a>
                                </div>
                            </div>

                            {{-- Form Filter --}}
                            <form method="GET" action="{{ route('backend.booking.index') }}" class="mb-3">
                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <select name="ruang_id" class="form-select">
                                            <option value="">-- Filter Ruangan --</option>
                                            @foreach ($ruangs as $data)
                                                <option value="{{ $data->id }}" {{ request('ruang_id') == $data->id ? 'selected' : '' }}>
                                                    {{ $data->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <input type="date" name="tanggal" class="form-control" value="{{ request('tanggal') }}">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <select name="status" class="form-select">
                                            <option value="">-- Filter Status --</option>
                                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="diterima" {{ request('status') == 'diterima' ? 'selected' : '' }}>Diterima</option>
                                            <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                                            <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <button type="submit" class="btn btn-sm btn-primary me-2">Terapkan Filter</button>
                                        <a href="{{ route('backend.booking.index') }}" class="btn btn-sm btn-info">Reset</a>
                                    </div>
                                </div>
                            </form>

                            {{-- Tabel Booking --}}
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Ruangan</th>
                                            <th>Penanggung Jawab</th>
                                            <th>Tanggal</th>
                                            <th>Jam Mulai</th>
                                            <th>Jam Selesai</th>
                                            <th>Keterangan</th>
                                            <th>Jumlah Orang</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($bookings as $data)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $data->ruang->nama ?? '-' }}</td>
                                                <td>{{ $data->user->name ?? '-' }}</td>
                                                <td>{{ $data->tanggal }}</td>
                                                <td>{{ \Carbon\Carbon::parse($data->jam_mulai)->format('h:i A') }}</td>
                                                <td>{{ \Carbon\Carbon::parse($data->jam_selesai)->format('h:i A') }}</td>
                                                <td>{{ $data->keterangan ?? '-' }}</td>
                                                <td>{{ $data->jumlah_orang ?? '-' }}</td>
                                                <td>{{ ucfirst($data->status) }}</td>
                                                <td>
                                                    <a href="{{ route('backend.booking.edit', $data->id) }}"
                                                        class="btn btn-sm btn-warning">Edit</a>
                                                    <a href="{{ route('backend.booking.show', $data->id) }}"
                                                        class="btn btn-sm btn-primary">Show</a>
                                                    <form action="{{ route('backend.booking.destroy', $data->id) }}"
                                                        method="POST" style="display:inline;" class="form-delete">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger btn-delete">Hapus</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center">Tidak ada data booking ditemukan.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div> {{-- end table-responsive --}}
                        </div> {{-- end card-body --}}
                    </div> {{-- end card --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault(); // cegah form submit otomatis
            const form = $(this).closest('form');

            Swal.fire({
                title: 'Yakin ingin menghapus booking ini?',
                text: "Data akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // submit form jika konfirmasi OK
                }
            });
        });
    </script>
@endpush
