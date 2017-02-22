<table class="codecubes-datatable">
	<thead>
		@include("datatable::partials.table-columns", ["columns" => $columns])
	</thead>
	@if($displayTFoot || $enableFilter)
	<tfoot>
		@include("datatable::partials.table-columns", ["columns" => $columns])
	</tfoot>
	@endif
</table>