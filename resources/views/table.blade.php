<table class="codecubes-datatable">
	<thead>
		<tr>
		@foreach($columns as $column)
		<th>{{ $column["alias"] }}</th>
		@endforeach
		</tr>
	</thead>
	@if($displayTFoot || $enableFilter)
	<tfoot>
		<tr>
		@foreach($columns as $column)
		<th>{{ $column["alias"] }}</th>
		@endforeach
		</tr>
	</tfoot>
	@endif
</table>