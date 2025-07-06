<tr>
  <th scope="row" width="35px">{{ $loopIndex }}</th>
  <td>{{ $item->code }}</td>
  <td>{{ $item->name }}</td>
  <td>{{ $item->type->name }}</td>
  <td>{{ toIndonesianCurrency($item->amount) }}</td>
  <td>{{ carbonTranslatedFormat($item->created_at) }}</td>
  <td>{{ carbonTranslatedFormat($item->updated_at) }}</td>
</tr>
