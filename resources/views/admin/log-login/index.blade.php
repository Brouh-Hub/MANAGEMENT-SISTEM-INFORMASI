@extends('layouts.main')
@section('content')

<div class="mb-3">
    <h4 class="fw-bold mb-1">{{ $title }}</h4>
    <p class="text-muted mb-0">{{ $subtitle }}</p>
</div>

<div class="card-panel">
    <div class="table-responsive">
        <table class="table align-middle" id="table" width="100%">
            <thead>
                <tr class="text-muted text-uppercase" style="font-size:11px;">
                    <th>No</th>
                    <th>Waktu</th>
                    <th>Email</th>
                    <th>Nama</th>
                    <th>IP</th>
                    <th>Status</th>
                    <th>Browser</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {
    $('#table').DataTable({
        serverSide: true,
        processing: true,
        order: [],
        ajax: { url: "{{ route('log-login.index') }}", method: 'GET' },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'waktu', name: 'created_at', searchable: false },
            { data: 'email', name: 'email' },
            { data: 'nama', name: 'user.name', orderable: false, searchable: false },
            { data: 'ip', name: 'ip' },
            { data: 'status_badge', name: 'status' },
            { data: 'user_agent', name: 'user_agent', orderable: false },
        ]
    });
});
</script>
@endpush
