Kami ingin menginformasikan bahwa ada pengguna yang baru saja login ke situs web Anda. Berikut adalah detail login pengguna tersebut:

*Detail Pengguna:*

  - *Alamat Email*: {{ $email_user ?? '-' }}
  - *Alamat IP*: {{ $ip_address ?? '-' }}
  - *Lokasi*: {{ $address ?? '-' }}
  - *Geolokasi*: {{ $geolocation ?? '-' }}
  - *Zona Waktu*: {{ $timezone ?? '-' }}
  - *Perangkat*: {{ $user_agent ?? '-' }}
  - *Waktu Login*: {{ carbonTranslatedFormat($created_at, 'd F Y H:i') }}
  - *Referer*: {{ $referer ?? '-' }}

Jika ini bukan aktivitas yang Anda kenali, sebagai tindakan pencegahan, kami sarankan Anda untuk segera memeriksa aktivitas pengguna tersebut dan melakukan tindakan yang diperlukan.

Terima kasih atas perhatian Anda.

Salam Hangat,

Nova