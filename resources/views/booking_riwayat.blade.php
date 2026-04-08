@extends('layouts.frontend')

@section('content')
    <div class="container py-5">
        <h2 class="mb-4 fw-bold text-center">Profile</h2>

        <div class="row justify-content-center mb-4">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body text-center">
                        <h4 class="mb-1">{{ Auth::user()->name }}</h4>
                        <div class="text-muted">{{ Auth::user()->email }}</div>
                        <div class="mt-2">
                            @if (Auth::user()->status === 'Siswa')
                                <div><strong>NISN:</strong> {{ Auth::user()->nisn ?? '-' }}</div>
                            @elseif (Auth::user()->status === 'Guru')
                                <div><strong>NIP:</strong> {{ Auth::user()->nip ?? '-' }}</div>
                            @endif
                            <div><strong>Status:</strong> {{ Auth::user()->status ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h4 class="mb-3 fw-semibold text-center">Riwayat Booking Anda</h4>

        {{-- Form Filter --}}
        <form method="GET" action="{{ route('booking_riwayat') }}" class="mb-3">
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
                    <a href="{{ route('booking_riwayat') }}" class="btn btn-sm btn-secondary">Reset</a>
                </div>
            </div>
        </form>

        @if ($booking->count())
            <div class="table-responsive shadow-sm rounded">
                <table class="table table-hover align-middle text-center">
                    <thead class="table-primary">
                        <tr>
                            <th>No</th>
                            <th>Ruangan</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Keterangan</th>
                            <th>Jumlah Orang</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($booking as $index => $data)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $data->ruang->nama }}</td>
                                <td>{{ \Carbon\Carbon::parse($data->tanggal)->translatedFormat('l, d F Y') }}</td>
                                <td>
                                    {{ \Carbon\Carbon::parse($data->jam_mulai)->format('h:i A') }} -
                                    {{ \Carbon\Carbon::parse($data->jam_selesai)->format('h:i A') }}
                                </td>
                                <td>{{ $data->keterangan ?? '-' }}</td>
                                <td>{{ $data->jumlah_orang ?? '-' }}</td>
                                <td>
                                    @switch($data->status)
                                        @case('pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @break

                                        @case('diterima')
                                            <span class="badge bg-primary">Diterima</span>
                                        @break

                                        @case('ditolak')
                                            <span class="badge bg-danger">Ditolak</span>
                                        @break

                                        @case('selesai')
                                            <span class="badge bg-success">Selesai</span>
                                        @break

                                        @default
                                            <span class="badge bg-secondary">Tidak Diketahui</span>
                                    @endswitch
                                </td>
                                <td>
                                    @if ($data->status !== 'selesai' && $data->status !== 'ditolak'
                                        && $data->status !== 'diterima')
                                        <button type="button" class="btn btn-sm btn-outline-warning"
                                            data-bs-toggle="modal" data-bs-target="#editBookingModal-{{ $data->id }}">
                                            Edit
                                        </button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @foreach ($booking as $data)
                <div class="modal fade" id="editBookingModal-{{ $data->id }}" tabindex="-1"
                    aria-labelledby="editBookingLabel-{{ $data->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content text-start">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editBookingLabel-{{ $data->id }}">
                                    Edit Booking
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <form method="POST" action="{{ route('booking.update', $data->id) }}">
                                @csrf
                                @method('PUT')
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Ruangan</label>
                                        <select name="ruang_id" class="form-select" required>
                                            @foreach ($ruangs as $ruang)
                                                <option value="{{ $ruang->id }}"
                                                    {{ $data->ruang_id == $ruang->id ? 'selected' : '' }}>
                                                    {{ $ruang->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Tanggal</label>
                                        <input type="date" name="tanggal" class="form-control"
                                            value="{{ $data->tanggal }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Jam Mulai</label>
                                        <input type="time" name="jam_mulai" class="form-control"
                                            value="{{ \Carbon\Carbon::parse($data->jam_mulai)->format('H:i') }}"
                                            required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Jam Selesai</label>
                                        <input type="time" name="jam_selesai" class="form-control"
                                            value="{{ \Carbon\Carbon::parse($data->jam_selesai)->format('H:i') }}"
                                            required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Keterangan</label>
                                        <input type="text" name="keterangan" class="form-control" maxlength="255"
                                            value="{{ $data->keterangan }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Jumlah Orang</label>
                                        <input type="number" name="jumlah_orang" class="form-control" min="1"
                                            value="{{ $data->jumlah_orang }}" required>
                                    </div>
                                    <div class="small text-muted">
                                        Jika jam sudah dibooking, sistem akan meminta Anda memilih waktu lain.
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="d-flex justify-content-center mt-3">
                {{ $booking->links('pagination::bootstrap-5') }}
            </div>
        @else
            <div class="alert alert-info text-center">
                Belum ada riwayat booking ruangan.
            </div>
        @endif
    </div>
@endsection
