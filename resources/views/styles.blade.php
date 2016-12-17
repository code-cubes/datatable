<link rel="stylesheet" type="text/css" href="{{ asset("codecubes/datatable/css/jquery.dataTables.min.css") }}">
{{-- custom search --}}
@if($customSearchSelector)
<style type="text/css">
{{ $prefix }} .dataTables_filter {
   display: none;
}
</style>
@endif