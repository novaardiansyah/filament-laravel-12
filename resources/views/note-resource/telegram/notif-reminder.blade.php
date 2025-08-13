@extends('layout.telegram.main')

@section('content')
Kami ingin mengingatkan Anda tentang catatan yang telah Anda buat. Berikut adalah detail catatan tersebut:

*Detail Catatan:*

  - *Judul*: {{ $title ?? '-' }}
  - *Tanggal*: {{ carbonTranslatedFormat($notification_at, 'd F Y H:i') }}
  - *Detail*: [{{ $code ?? '-' }}]({{ $view_link ?? '#' }})

Jika Anda memiliki pertanyaan atau perlu bantuan lebih lanjut, jangan ragu untuk menghubungi kami.
@endsection