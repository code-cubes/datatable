<script type="text/javascript" src="{{ asset("codecubes/datatable/js/jquery.dataTables.min.js") }}"></script>
<script>
$(document).ready(function() {
	@if ($enableFilter)
    $('.codecubes-datatable tfoot th').each(function () {
        var title = $(this).text();
        $(this).html('<input class="codecubes-filters" type="text" placeholder="Search ' + title + '" />');
    });
    @endif

    var config = {!! json_encode($config) !!};
    config.ajax = "{{ $url }}";
    config.columns = [
        @foreach($columns as $column)
            { 
                "data": "{{ $column["mappedName"] }}" 
                @if($column["render"]) , 
                "render": function (data, type, row, meta) {
                    return "{!! $column["render"] !!}";
                }
                @endif 
            }
            @if(! $loop->last)
            ,
            @endif
        @endforeach
    ];
    var dataTable = $('.codecubes-datatable').DataTable(config);

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

    // custom search
    @if($customSearchSelector)
    $("{{ $customSearchSelector }}").on('keyup change', function(){
        dataTable.search($(this).val()).draw() ;
    });
    @endif
});
</script>