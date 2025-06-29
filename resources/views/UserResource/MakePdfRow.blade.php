@php
  $roles = $item->roles->pluck('name')->implode(', ');
  $roles = collect(explode(',', $roles))
    ->map(fn($role) => ucwords(str_replace('_', ' ', $role)))
    ->implode(', ');
@endphp

<tr>
  <th scope="row" width="35px">{{ $loopIndex }}</th>
  <td>{{ $item->name }}</td>
  <td>{{ $roles }}</td>
  <td>{{ $item->email }}</td>
  <td>{{ $item->email_verified_at ? carbonTranslatedFormat($item->email_verified_at) : '-' }}</td>
  <td>{{ carbonTranslatedFormat($item->created_at) }}</td>
  <td>{{ carbonTranslatedFormat($item->updated_at) }}</td>
</tr>
