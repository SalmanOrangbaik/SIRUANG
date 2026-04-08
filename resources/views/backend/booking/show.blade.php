@extends('layouts.backend')

@section('content')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-8 grid-margin stretch-card w-100">
                <div class="card">
                    <div class="card-body performane-indicator-card">
                        <h4 class="card-title mb-4">Detail Booking</h4>

                        <div class="mb-3">
                            <strong>Ruangan:</strong> {{ $booking->ruang->nama ?? '-' }}
                        </div>
                        <div class="mb-3">
                            <strong>User:</strong> {{ $booking->user->name ?? '-' }}
                        </div>
                        <div class="mb-3">
                            <strong>Tanggal:</strong> {{ $booking->tanggal }}
                        </div>
                        <div class="mb-3">
                            <strong>Jam Mulai:</strong> {{ \Carbon\Carbon::createFromFormat('H:i:s', $booking->jam_mulai)->format('h:i A') }}
                        </div>
                        <div class="mb-3">
                            <strong>Jam Selesai:</strong> {{ \Carbon\Carbon::createFromFormat('H:i:s', $booking->jam_selesai)->format('h:i A') }}
                        </div>
                        <div class="mb-3">
                            <strong>Keterangan:</strong> {{ $booking->keterangan ?? '-' }}
                        </div>
                        <div class="mb-3">
                            <strong>Jumlah Orang:</strong> {{ $booking->jumlah_orang ?? '-' }}
                        </div>
                        <div class="mb-3">
                            <strong>Status:</strong>
                            @if ($booking->status === 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @elseif ($booking->status === 'ditolak')
                                <span class="badge bg-danger">Ditolak</span>
                            @elseif ($booking->status === 'diterima')
                                <span class="badge bg-primary">Diterima</span>
                            @else
                                <span class="badge bg-success">Selesai</span>
                            @endif
                        </div>
                        <a href="{{ route('backend.booking.index') }}" class="btn btn-secondary mt-3">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
