@extends('layout.pdf')

@section('content')
  <h1 class="bottom-0">Daftar Pengguna</h1>
  <p class="vertical-0 text-muted">Dicetak Pada: {{ $now }}</p>
  <hr>

  <table class="table table-bordered table-sm">
    <thead>
      <tr>
        <th scope="col">#</th>
        <th scope="col">Nama Lengkap</th>
        <th scope="col">Role</th>
        <th scope="col">Email</th>
        <th scope="col">Verifikasi Pada</th>
        <th scope="col">Dibuat pada</th>
        <th scope="col">Diubah pada</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($data as $item)
        @php
          $roles = $item->roles->pluck('name')->implode(', ');
          $roles = collect(explode(',', $roles))
            ->map(fn($role) => ucwords(str_replace('_', ' ', $role)))
            ->implode(', ');
        @endphp

        <tr>
          <th scope="row" width="35px">{{ $loop->iteration }}</th>
          <td>{{ $item->name }}</td>
          <td>{{ $roles }}</td>
          <td>{{ $item->email }}</td>
          <td>{{ $item->email_verified_at ? carbonTranslatedFormat($item->email_verified_at) : '-' }}</td>
          <td>{{ carbonTranslatedFormat($item->created_at) }}</td>
          <td>{{ carbonTranslatedFormat($item->updated_at) }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
@endsection