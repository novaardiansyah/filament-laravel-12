@include('layout.header')

<h1 class="bottom-0">Tagihan Pembayaran</h1>
<p class="vertical-0 text-muted">Dicetak Pada: {{ $now }}</p>
<hr>

<table class="table table-bordered table-sm">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">ID Tagihan</th>
      <th scope="col">Produk/Layanan</th>
      <th scope="col">Tempo</th>
      <th scope="col">Terjadwal</th>
      <th scope="col">Akun Kas</th>
      <th scope="col">Tagihan</th>
    </tr>
  </thead>
  <tbody>
