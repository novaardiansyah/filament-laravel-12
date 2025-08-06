@php
  $paymentDate = $data->payment->date ?? null;
@endphp

<tr>
  <th scope="row" width="35px">{{ $loopIndex }}</th>
  <td style="width: 80px;">{{ $data->code }}</td>
  <td>{{ $data->item->name }}</td>
  <td style="width: 80px; text-align: center;">{{ carbonTranslatedFormat($data->due_date, 'd M Y') }}</td>
  <td style="width: 80px; text-align: center;">{{ $paymentDate ? carbonTranslatedFormat($paymentDate, 'd M Y') : '-' }}</td>
  <td style="width: 80px;">{{ $data->paymentAccount->name }}</td>
  <td style="width: 85px; text-align: right; white-space: nowrap;">
    {{ $data->amount ? toIndonesianCurrency($data->amount ?? 0) : '-' }}
  </td>
</tr>
