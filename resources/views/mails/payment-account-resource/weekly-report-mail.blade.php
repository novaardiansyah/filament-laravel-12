@extends('mails.layout.main-light')

@section('title')
  {{ $data['subject'] }}
@endsection

@section('header')
  Hai {{ explode(' ', config('app.author_name'))[0] }},
@endsection

@section('content')
  <p>
    Kami ingin menginformasikan bahwa berikut adalah laporan keuangan mingguan Anda untuk periode {{ $data['periode'] ?? '-' }}, sebagai berikut:
  </p>

  <div class="card">
    <div class="group">
      <h4>Detail laporan keuangan:</h4>
      <ul class="list-flush">
        <li>
          <strong>Pengeluaran</strong>: {{ $data['total_expense'] ? toIndonesianCurrency($data['total_expense']) : '-' }} {{ $data['count_expense'] ? "({$data['count_expense']}x Trx)" : '' }}
        </li>
        <li>
          <strong>Pemasukan</strong>: {{ $data['total_income'] ? toIndonesianCurrency($data['total_income']) : '-' }} {{ $data['count_income'] ? "({$data['count_income']}x Trx)" : '' }}
        </li>
        <li>
          <strong>Avg. Pengeluaran</strong>: {{ $data['avg_expense'] ? toIndonesianCurrency($data['avg_expense']) : '-' }}
        </li>
        <li>
          <strong>Avg. Pemasukan</strong>: {{ $data['avg_income'] ? toIndonesianCurrency($data['avg_income']) : '-' }}
        </li>
        <li>
          <strong>Sisa Saldo: <span class="text-primary">{{ $data['sisa_saldo'] ? toIndonesianCurrency($data['sisa_saldo']) : '-' }}</span></strong>
        </li>
      </ul>
    </div>
  </div>
  
  <p>
    Harap diperhatikan bahwa laporan ini mencakup semua transaksi yang dilakukan selama periode tersebut. Jika Anda memiliki pertanyaan atau memerlukan klarifikasi lebih lanjut, jangan ragu untuk menghubungi kami.
  </p>
@endsection