<table style="width:100%;border-collapse:collapse" class="w-full text-sm"><thead><tr>@foreach($head as $h)<th style="border:1px solid #000;padding:4px;background:#000;color:#fff">{{ $h }}</th>@endforeach</tr></thead>
@foreach($rows as $row)<tr>@foreach($row as $c)<td style="border:1px solid #000;padding:4px">{{ $c }}</td>@endforeach</tr>@endforeach</table>
