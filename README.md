# DataTable
jQuery Datatable for Laravel applications

Until now it's under development and not stable yet. and not avaliable as stable package to use.

##### Installation:
1. Create directory in Laravel root directory (as an example "extensions")
2. In Laravel composer.json you will find "classmap" array add your created directory to it example:
    ```js
    "autoload": {
        "classmap": [
            "database",
            "extensions"
        ],
        "psr-4": {
            "App\\": "app/"
        }
    }
    ```
3. Create sub directory called CodeCubes for created directory in point (1) (as an example "extensions/CodeCubes")
4. download this package inside this sub directory and make sure that directory name for this package called datatable
5. open config/app.php and in providers array add 
    ```php
    CodeCubes\DataTable\Providers\DataTableServiceProvider::class,
    ```
6. run this command on laravel root directory
    ```sh
    php artisan vendor:publish --provider "CodeCubes\DataTable\Providers\DataTableServiceProvider"
    ```
    
##### Usage

###### In controller

example (1):
```php
/**
 * @route admin.home (route alias)
 */
public function list () {
    $user = new App\User;
    
    $dataTable = new CodeCubes\DataTable\DataTable($user, "admin.home", [
        // columns name or column name => column Alias
        "name", "email", "created_at" => "created At", 
    ]);
    // in case of that this method is responsible for rendering table
    // and in the same time return JSON data required by datatable.
    return $dataTable->response(request(), view('welcome', compact(["dataTable"])));
}
```

example (2)
```php
public function __construct () {
    $user = new App\User;
	$this->dataTable = new CodeCubes\DataTable\DataTable($user, "admin.home.json", [
        "name", "email", "created_at" => "created At",
	]);
}

/**
 * @route admin.home (route alias)
 */
public function list () {
	return view('welcome', compact(["dataTable" => $this->dataTable]));
}

/**
 * @route admin.home.json (route alias)
 */
public function json () {
    $this->dataTable->response(request());
}
```
example (3) (Relations):
```php
/**
 * @route admin.home (route alias)
 */
public function list () {
    $user = new App\User;
    // in case of relations you must use joins
    $user = $user->join("roles", "roles.id", "=", "users.role_id", "left");

	$dataTable = new CodeCubes\DataTable\DataTable($user, "admin.home", [
	    // here you must enter {TABLE_NAME}.{COLUMN_NAME}
		"users.name" => "Name",
		"users.email" => "Email",
		"roles.name" => "Role",
    ]);
    // in case of that this method is responsible for rendering table
    // and in the same time return JSON data required by datatable.
    return $dataTable->response(request(), view('welcome', compact(["dataTable"])));
}
```

for more clarifications:
```php
/**
 * Create new DataTable instance
 *
 * @param Illuminate\Database\Eloquent\Model|Illuminate\Database\Eloquent\Builder $query
 * @param string $route
 * @param mixed[] $columns
 */
public function __construct ($query, $route, $columns);

/**
 * response method
 *
 * used to be called and returned in user defined method as a responser
 *
 * @param Illuminate\Http\Request $request
 * @param mixed $regularResponse user defined response
 * @return mixed either regular response or array
 */
public function response (Request $request, $regularResponse = null);
```

###### In views
```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <!-- echo required styles by datatable -->
    {!! $dataTable->styles() !!}
    <!-- this package depend on jQuery so you must include it -->
    <script type="text/javascript" language="javascript" src="//code.jquery.com/jquery-1.12.4.js">
    </script>
    <!-- echo required scripts by datatable -->
    {!! $dataTable->scripts() !!}
</head>
<body class="dt-example">
    <br />
    <div class="container">
        <!-- render "datatable" table -->
        {!! $dataTable->render() !!}
    </div>
</body>
</html>
```