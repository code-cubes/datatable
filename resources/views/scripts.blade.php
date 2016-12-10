<script type="text/javascript" src="{{ asset("codecubes/datatable/js/jquery.dataTables.min.js") }}"></script>
<script>
$(document).ready(function() {
	@if ($enableFilter)
    $('.codecubes-datatable tfoot th').each(function () {
        var title = $(this).text();
        $(this).html('<input class="codecubes-filters" type="text" placeholder="Search ' + title + '" />');
    });
    @endif

    var dataTable = $('.codecubes-datatable').DataTable( {
        @foreach($config as $key => $value)
			"{{ $key }}": "{{ $value }}",
		@endforeach
        "ajax": "{{ $url }}",
        "columns": [
        	@foreach($columns as $column)
				{ "data": "{{ $column["mappedName"] }}" }
				@if(! $loop->last)
				,
				@endif
			@endforeach
        ]
    });

	@if ($enableFilter)
    // Apply the filter
    dataTable.columns().every( function () {
        var that = this;
        $('input', this.footer()).on('keyup change', function () {
            if (that.search() !== this.value) {
                that.search(this.value).draw();
            }
        });
    });
    @endif
});
</script>