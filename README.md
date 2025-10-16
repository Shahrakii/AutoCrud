Laravel Auto Form Generator

Automatically generate CRUD forms in Laravel for any table. Supports dynamic input types, file uploads, enums, foreign keys, TailwindCSS, Bootstrap, and full auto-import of styles.

Features

Auto-create/edit forms from your database schema.

Dynamic input generation:

text, number, email, password

textarea

select (enum or foreign key)

checkbox

file uploads

Auto-applies TailwindCSS or Bootstrap styles.

Auto CDN import for selected framework.

Table-specific & global filters.

Easy integration into Laravel controllers & models.

Supports dynamic options for selects (enum or foreign table).

Installation

Copy Helper Files

Place these files inside app/Helpers:

ColumnInputMapper.php

InputStyles.php

AutoControllerGenerator.php

Filter.php

SchemaExtractor.php

Set Framework in .env

CSS_FRAMEWORK=tailwind   # Options: tailwind, bootstrap


Create a migration for testing all input types

php artisan make:migration create_test_inputs_table --create=test_inputs


Example migration:

Schema::create('test_inputs', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->string('email');
    $table->string('password');
    $table->boolean('is_active')->default(true);
    $table->integer('age')->nullable();
    $table->string('profile_picture')->nullable(); // File input
    $table->enum('status', ['active', 'inactive', 'pending']);
    $table->timestamps();
});

Model Setup

Example: User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'status',
        'profile_picture',
    ];
}


$fillable: columns that can be mass-assigned.

Adjust to match your table columns.

Controller Setup

Example: UserController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\AutoControllerGenerator;
use App\Helpers\Filter;
use App\Helpers\SchemaExtractor;

class UserController extends Controller
{
    protected string $modelClass = User::class;

    public function __construct()
    {
        AutoControllerGenerator::init($this->modelClass);
        Filter::addTableFilter('users', 'remember_token'); // Optional filter
    }

    public function create()
    {
        return AutoControllerGenerator::handleView(__FUNCTION__);
    }

    public function store(Request $request)
    {
        AutoControllerGenerator::handleRequest(__FUNCTION__, $request);
        return redirect()->route('users.create')->with('success', 'User created successfully.');
    }

    public function edit($id)
    {
        return AutoControllerGenerator::handleView(__FUNCTION__, $id);
    }

    public function update(Request $request, $id)
    {
        AutoControllerGenerator::handleRequest(__FUNCTION__, $request, $id);
        return redirect()->route('users.edit', $id)->with('success', 'User updated successfully.');
    }

    public function index()
    {
        $table = (new $this->modelClass)->getTable();
        $columns = Filter::filterColumns(SchemaExtractor::getTableColumns($table), $table);
        $data = $this->modelClass::select(array_column($columns, 'name'))->paginate(10);

        return view('autocontroller.index', compact('columns', 'data'));
    }
}

Blade Form Example
<html>
<head>
    {!! \App\Helpers\ColumnInputMapper::getCDN() !!}
</head>
<body>
    <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data">
        @csrf
        @foreach($columns as $column)
        <div class="mb-3">
            <label>{{ ucfirst($column['name']) }}</label>
            {!! \App\Helpers\ColumnInputMapper::getInput($column, $values[$column['name']] ?? null) !!}
        </div>
        @endforeach
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</body>
</html>


getCDN(): automatically imports Tailwind or Bootstrap.

getInput(): generates correct input type with styles.

Input Styles Customization

Located in app/Helpers/InputStyles.php.

Example Tailwind styles:

return match(env('CSS_FRAMEWORK','tailwind')) {
    'tailwind' => [
        'text' => '!block !w-full !rounded-xl !border !border-gray-200 !px-4 !py-2 !bg-white !placeholder-gray-400 !text-gray-800 !shadow-sm !transition-colors !duration-150 !focus:!outline-none !focus:!ring-4 !focus:!ring-blue-100 !focus:!border-blue-400',
        'number' => '...',
        'email' => '...',
        'password' => '...',
        'select' => '...',
        'textarea' => '...',
        'checkbox' => '...',
        'file' => '...'
    ],
    'bootstrap' => [
        'text' => 'form-control',
        'number' => 'form-control',
        'email' => 'form-control',
        'password' => 'form-control',
        'select' => 'form-select',
        'textarea' => 'form-control',
        'checkbox' => 'form-check-input',
        'file' => 'form-control'
    ],
    default => []
};


Add !important via ! prefix in Tailwind.

Customize colors, borders, padding, shadows per project style.

How to Use in Projects

Create a migration.

Define a model and $fillable.

Create a controller with AutoControllerGenerator.

Use ColumnInputMapper::getInput in Blade.

Set CSS_FRAMEWORK in .env.

Optionally, add table-specific filters using Filter::addTableFilter($table, $column).

Available Commands & Their Purpose
Command	Purpose
php artisan make:migration	Creates a migration for a table.
php artisan migrate	Executes all migrations and creates tables.
php artisan make:model	Creates a new Eloquent model.
php artisan make:controller	Creates a controller class.
Filter::addTableFilter($table, $column)	Hide column from auto-generated form.
Filter::addGlobalFilter($column)	Hide column globally across tables.
AutoControllerGenerator::handleView($method, $id=null)	Generates the create/edit form view.
AutoControllerGenerator::handleRequest($method, $request, $id=null)	Handles store/update requests automatically.
ColumnInputMapper::getInput($column, $value)	Returns HTML input element with styles.
ColumnInputMapper::getCDN()	Returns proper CDN for Tailwind/Bootstrap.
Notes

File uploads require enctype="multipart/form-data".

Enum/select values are auto-populated from the DB schema or foreign keys.

Tailwind classes use ! for !important overrides.

Fully compatible with Laravel 12+, PHP 8.2+.
