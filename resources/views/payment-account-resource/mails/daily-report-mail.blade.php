@extends('layout.mails.main-light')

@section('title')
  {{ $data['subject'] }}
@endsection

@section('header')
  Hai {{ explode(' ', config('app.author_name'))[0] }},
@endsection

@section('content')
  <p>Berikut adalah ringkasan laporan pada akun keuangan Anda untuk periode {{ $data['date'] }}.</p>

  <div class="card">
    <div class="group">
      <h4>Informasi Terkait</h4>
      <ul class="list-flush">
        @php
          $total = 0;
        @endphp

        @foreach ($data['payment_accounts'] as $item)
          <li><strong>{{ $item['name'] }}</strong>: {{ toIndonesianCurrency($item['deposit'] ?? 0) }}</li>

          @php
            $total += $item['deposit'] ?? 0;
          @endphp
        @endforeach
        <li><strong>Total: <span class="text-primary">{{ toIndonesianCurrency($total) }}</span></strong></li>
      </ul>
    </div>
  </div>
@endsection
