<tr>
	@foreach($columns as $column)
	<th>{{ $column["alias"] }}</th>
	@endforeach
</tr>