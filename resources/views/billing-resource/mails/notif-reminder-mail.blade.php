@extends('layout.mails.main-light')

@section('title')
  {{ $data['subject'] }}
@endsection

@section('header')
  Hai {{ explode(' ', config('app.author_name'))[0] }},
@endsection

@section('content')
  <p>
    Kami ingin memberitahukan bahwa terdapat tagihan yang akan jatuh tempo dalam waktu dekat, sebagai pengingat untuk Anda. Berikut adalah ringkasan informasi tagihan yang perlu diperhatikan.
  </p>

  @php
    $dailyCount = (int) $data['summary']['daily_count'] ?? 0;
    $daily      = $dailyCount > 0 ? toIndonesianCurrency($data['summary']['daily'] ?? 0) . "({$dailyCount}x Trx)" : '-';

    $weeklyCount = (int) $data['summary']['weekly_count'] ?? 0;
    $weekly      = $weeklyCount > 0 ? toIndonesianCurrency($data['summary']['weekly'] ?? 0) . "({$weeklyCount}x Trx)" : '-';
    
    $monthlyCount = (int) $data['summary']['monthly_count'] ?? 0;
    $monthly      = $monthlyCount > 0 ? toIndonesianCurrency($data['summary']['monthly'] ?? 0) . "({$monthlyCount}x Trx)" : '-';

    $total = intval($data['summary']['daily'] ?? 0) + intval($data['summary']['weekly'] ?? 0) + intval($data['summary']['monthly'] ?? 0);
  @endphp

  <div class="card mb-2">
    <div class="group">
      <h4>Informasi Tagihan</h4>
      <ul class="list-flush">
        <li>
          <strong>Harian:</strong>
          {{ $daily }}
        </li>
        <li>
          <strong>Mingguan:</strong>
          {{ $weekly }}
        </li>
        <li>
          <strong>Bulanan:</strong>
          {{ $monthly }}
        </li>
        <li>
          <strong>Total Tagihan</strong>:
          {{ toIndonesianCurrency($total) }}
        </li>
      </ul>
    </div>
  </div>

  <p>Pengingat ini akan dikirimkan selama <strong>{{ $data['reminder_days'] }}</strong> berturut-turut sebelum jatuh tempo, sesuai kebijakan sistem Anda. Penyesuaian jadwal pengingat dapat dilakukan jika diperlukan.</p>
@endsection