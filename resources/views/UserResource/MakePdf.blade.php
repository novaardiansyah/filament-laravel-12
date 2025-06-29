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
      {!! $rows !!}
    </tbody>
  </table>
@endsection