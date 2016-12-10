<?php

namespace CodeCubes\DataTable;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use CodeCubes\DataTable\Contracts\DataTableContract;
use Schema;

class DataTable implements DataTableContract {

	const SEPARATOR = "___";
	protected $route = null;
	protected $query = null;
	protected $config = null;
	protected $columns = [];
	
	/**
	 * Create new DataTable instance
	 *
	 * @param Illuminate\Database\Eloquent\Model|Illuminate\Database\Eloquent\Builder $query
	 * @param string $route
	 * @param mixed[] $columns
	 */
	public function __construct ($query, $route, $columns) {
		$config = config()->get("codecubes.datatable");
		$this->config = $config ?: [
			"processing"	=>	true,
			"serverSide"	=>	true,
			"enableFilter"	=>	true,
			"displaTFoot"	=>	false,
		];
		$this->setRoute($route);
		$this->setColumns($columns);
		$this->setQuery($query);
	}
	
	/**
	 * setRoute method
	 *
	 * @param string $route
	 * @return void
	 */
	protected function setRoute ($route) {
		if (! is_string($route)) {
			throw new \InvalidArgumentException("\$route must be in string");
		}
		$this->route = $route;
	}

	/**
	 * setQuery method
	 *
	 * @param Illuminate\Database\Eloquent\Model|Illuminate\Database\Eloquent\Builder $query
	 * @return void
	 */
	protected function setQuery ($query) {
		$columns = $this->columns;
		array_walk($columns, function (&$record, $key) {
			$record = $record["name"] . " AS " . $record["mappedName"];
		});
		$query = $query->select($columns);
		$this->query = $query;
	}

	/**
	 * getMappedColumnName method
	 *
	 * convert column name from `table`.`column` to be in form `table`{SEPARATOR}`column` because datatable
	 * consider "." as nested objects which we don't mean by ".".
	 *
	 * @param string $columnName
	 * @return string
	 */
	protected function getMappedColumnName ($columnName) {
		return str_replace(".", self::SEPARATOR, $columnName);
	}

	/**
	 * getFullQualifiedColumnName method
	 *
	 * convert column name from `table`{SEPARATOR}`column` to be in form `table`.`column` reset column name
	 * to it's original form.
	 *
	 * @param string $columnName
	 * @return string
	 */
	protected function getFullQualifiedColumnName ($mappedColumnName) {
		return str_replace(self::SEPARATOR, ".", $mappedColumnName);
	}

	/**
	 * setColumns method
	 *
	 * processed user entered columns and set it in expected form
	 *
	 * @param mixed[] $columns
	 * @return void
	 */
	protected function setColumns (array $columns) {
		$enhancedColumns = [];
		foreach ($columns as $key => $value) {
			if (is_numeric($key)) {
				$this->add($value);
			} else {
				$this->add($key, $value);
			}
		}
	}

	/**
	 * add method alias for addColumn
	 *
	 * add Column to columns in expected form
	 *
	 * @param string $columnName
	 * @param string|null $columnAlias
	 * @return void
	 */
	protected function add ($columnName, $columnAlias = null) {
		$columnAlias = $columnAlias ?: $columnName;

		$this->columns[] = [
			"name"			=>	$columnName,
			"mappedName"	=>	$this->getMappedColumnName($columnName),
			"alias"			=>	$columnAlias,
		];
	}

	/**
	 * render method alias for renderTable
	 *
	 * render datatable table
	 *
	 * @return Illuminate\View\View
	 */
	public function render () {
		$data = [
			"displayTFoot" => config()->get("codecubes.datatable.displayTFoot", false),
			"enableFilter" => config()->get("codecubes.datatable.enableFilter", false),
			"columns" => $this->columns,
		];
		return view("datatable::table", $data);
	}

	/**
	 * scripts method
	 *
	 * to handle datatable javascripts part
	 *
	 * @return Illuminate\View\View
	 */
	public function scripts () {
		$data = [
			"enableFilter" => config()->get("codecubes.datatable.enableFilter", false),
			"config" => $this->config,
			"url" => route($this->route),
			"columns" => $this->columns,
		];
		return view("datatable::scripts", $data);
	}

	/**
	 * styles method
	 *
	 * to handle datatable styles part
	 *
	 * @return Illuminate\View\View
	 */
	public function styles () {
		return view("datatable::styles");
	}

	/**
	 * response method
	 *
	 * used to be called and returned in user defined method as a responser
	 *
	 * @param Illuminate\Http\Request $request
	 * @param mixed $regularResponse
	 * @return mixed
	 */
	public function response (Request $request, $regularResponse = null) {
		if ($regularResponse && ! ($request->ajax() && $request->wantsJson())) {
			return $regularResponse;
		} else {
			return $this->responseData($request);
		}
	}

	/**
	 * responseData method
	 *
	 * generate JSON response for datatable actionsand requests
	 *
	 * @param Illuminate\Http\Request $request
	 * @return array
	 */
	protected function responseData (Request $request) {
		$offset = $request->input("start", 0);
		$limit  = $request->input("length", 10);
		$search = $request->input("search");
		$order = $request->input("order"); 
		$totalCount = $this->query->count();

		$this->search($search);

		if (config()->get("codecubes.datatable.enableFilter", false)) {
			$filter = $request->input("columns"); 
			$this->filter($filter);
		}

		$filteredCount = $this->query->count();

		$this->order($order);

		$this->paginate($offset, $limit);

		$tblData = $this->query->get();

		$data = [
			"draw"	=>	intval($request->input("draw")),
			"recordsTotal"	=>	intval($totalCount),
			"recordsFiltered"	=>	intval($filteredCount),
			"data"	=>	$tblData,
		];

		return $data;
	}

	/**
	 * order method
	 *
	 * handle datatable table ordering
	 *
	 * @param array $order
	 * @return void
	 */
	protected function order (array $order) {
		$columns = $this->columns;
		$columnIndex = array_get($order, "0.column", -1);
		$orderDirection = array_get($order, "0.dir", null);
    	if ($columnIndex >= 0 && ! empty($orderDirection)) {
    		if (isset($columns[$columnIndex]["mappedName"])) {
    			$columnName = $columns[$columnIndex]["name"];
				$this->query = $this->query->orderBy($columnName, $orderDirection);
    		}
    	}
	}
	
	/**
	 * search method
	 *
	 * handle datatable table search
	 *
	 * @param array $search
	 * @return void
	 */
	protected function search (array $search) {
		$keyword = array_get($search, "value", null);
		if (! empty($keyword)) {
    		$keyword = "%" . $keyword . "%";
    		$columns = $this->columns;
    		$this->query = $this->query->where(function ($query) use ($keyword, $columns) {
    			foreach ($columns as $column) {
    				$columnName = $column["name"];
    				$query->orWhere($columnName, "like", $keyword);
    			}
		 	});
    	}
	}
	
	/**
	 * filter method
	 *
	 * handle datatable table filtering
	 *
	 * @param array $filter
	 * @return void
	 */
	protected function filter (array $filter) {
		$columns = $this->columns;
		$this->query = $this->query->where(function ($query) use ($filter, $columns) {		
			foreach ($filter as $index => $record) {
				$keyword = array_get($filter, "$index.search.value", null);
				if (! empty($keyword)) {
					$columnName = array_get($columns, "$index.name", null);
					if (! empty($columnName)) {
						$keyword = "%" . $keyword . "%";
						$query->where($columnName, "like", $keyword);
					}
				}
			}
	 	});
	}

	/**
	 * paginate method
	 *
	 * handle datatable table paginating
	 *
	 * @param int $offset
	 * @param int $limit
	 * @return void
	 */
	protected function paginate ($offset, $limit) {
		$this->query = $this->query->offset($offset)->limit($limit);
	}
}