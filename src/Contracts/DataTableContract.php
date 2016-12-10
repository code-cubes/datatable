<?php

namespace CodeCubes\DataTable\Contracts;

use Illuminate\Http\Request;

interface DataTableContract {

	/**
	 * render method
	 *
	 * to be called in blade to render datatable table
	 *
	 * @return Illuminate\View\View
	 */
	public function render ();

	/**
	 * scripts method
	 *
	 * to be called with scripts to apply datatable js over rendered table
	 *
	 * @return Illuminate\View\View
	 */
	public function scripts ();

	/**
	 * response method
	 *
	 * this method return regualer response if request not via ajax and $regualerResponse not null 
	 * else will return JSON data to feed datatable.
	 *
	 * @return Illuminate\Http\Response | JSON
	 */
	public function response (Request $request, $regularResponse = null);

}