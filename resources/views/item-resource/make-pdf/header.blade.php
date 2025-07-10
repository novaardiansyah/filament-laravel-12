@include('layout.header')

<h1 class="bottom-0">Produk & Layanan</h1>
<p class="vertical-0 text-muted">Dicetak Pada: {{ $now }}</p>
<hr>

<table class="table table-bordered table-sm">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Kode SKU</th>
      <th scope="col">Nama</th>
      <th scope="col">Jenis</th>
      <th scope="col">Harga (*satuan)</th>
      <th scope="col">Dibuat pada</th>
      <th scope="col">Diubah pada</th>
    </tr>
  </thead>
  <tbody>
