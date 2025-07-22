@php
  $income  = $record->type_id == 2 ? toIndonesianCurrency($record->income ?? 0) : '';
  $expense = $record->type_id == 1 ? toIndonesianCurrency($record->expense ?? 0) : '';

  $amount = '';

  if ($record->type_id != 1 && $record->type_id != 2) {
    $amount = toIndonesianCurrency($record->amount ?? 0);
  }
@endphp

<tr>
  <th scope="row" width="35px">{{ $loopIndex }}</th>
  <td>{{ $record->code }}</td>
  <td>{{ carbonTranslatedFormat($record->date, 'd M Y') }}</td>
  <td>{{ $record->name }}</td>
  <td>{{ $amount }}</td>
  <td>{{ $income }}</td>
  <td>{{ $expense }}</td>
</tr>
