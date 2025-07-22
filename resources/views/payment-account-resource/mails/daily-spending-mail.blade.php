@extends('layout.mails.main-light')

@section('title')
  {{ $data['subject'] }}
@endsection

@section('header')
  Hai {{ explode(' ', config('app.author_name'))[0] }},
@endsection

@section('content')
  <p>Kami ingin menginformasikan bahwa pengeluaran Anda hari ini, {{ $data['date'] }}, telah mencapai atau melebihi batas limit harian yang telah ditentukan.</p>

  <div class="card">
    <div class="group">
      <h4>Detail Pengeluaran Hari Ini:</h4>
      <ul class="list-flush">
        <li>
          <strong>Pengeluaran</strong>: {{ toIndonesianCurrency($data['total_expense'] ?? 0) }}
        </li>
        <li>
          <strong>Limit harian</strong>: {{ toIndonesianCurrency($data['limit'] ?? 0) }}
        </li>
      </ul>
    </div>
  </div>

  <p>Mohon perhatikan agar transaksi berikutnya dapat disesuaikan dengan kebijakan limit harian Anda. Jika Anda membutuhkan penyesuaian terhadap limit pengeluaran, silakan hubungi admin atau lakukan pengaturan di aplikasi.</p>
@endsection
