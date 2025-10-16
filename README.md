<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laravel Auto Form Generator</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; padding: 2rem; background-color: #f9fafb; color: #111827;">

<h1 style="color: #1f2937; margin-bottom:1rem;">Laravel Auto Form Generator</h1>
<p>Automatically generate CRUD forms in Laravel for <strong>any table</strong>. Supports dynamic input types, file uploads, enums, foreign keys, TailwindCSS, Bootstrap, and auto-import of styles.</p>

<h2 style="color: #1f2937; margin-top:2rem;">Features</h2>
<ul>
    <li>Auto-create/edit forms from your database schema.</li>
    <li>Dynamic input types:
        <ul>
            <li>text, number, email, password</li>
            <li>textarea</li>
            <li>select (enum or foreign key)</li>
            <li>checkbox</li>
            <li>file uploads</li>
        </ul>
    </li>
    <li>Auto-applies <strong>TailwindCSS</strong> or <strong>Bootstrap</strong> styles.</li>
    <li>Auto CDN import for selected framework.</li>
    <li>Table-specific & global filters.</li>
    <li>Easy integration into Laravel controllers & models.</li>
    <li>Supports dynamic options for selects (enum or foreign table).</li>
</ul>

<h2 style="color: #1f2937; margin-top:2rem;">Installation</h2>

<h3>1. Copy Helper Files</h3>
<p>Place these files inside <code>app/Helpers</code>:</p>
<ul>
    <li>ColumnInputMapper.php</li>
    <li>InputStyles.php</li>
    <li>AutoControllerGenerator.php</li>
    <li>Filter.php</li>
    <li>SchemaExtractor.php</li>
    <li>ValidationGenerator.php</li>
    <li>InputBlueprints.php</li>
</ul>

<h3>2. Set Framework in <code>.env</code></h3>
<pre style="background:#f3f4f6; padding:1rem; border-radius:0.5rem;"><code>CSS_FRAMEWORK=tailwind # Options: tailwind, bootstrap</code></pre>

<h3>3. Create Migration for Testing</h3>
<pre style="background:#f3f4f6; padding:1rem; border-radius:0.5rem;"><code>php artisan make:migration create_test_inputs_table --create=test_inputs</code></pre>

<h4>Example Migration:</h4>
<pre style="background:#f3f4f6; padding:1rem; border-radius:0.5rem;"><code>Schema::create('test_inputs', function (Blueprint $table) {
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
});</code></pre>

<h3>4. Model Setup</h3>
<pre style="background:#f3f4f6; padding:1rem; border-radius:0.5rem;"><code>namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model {
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'password', 'role_id', 'status', 'profile_picture',
    ];
}</code></pre>

<h3>5. Controller Setup</h3>
<pre style="background:#f3f4f6; padding:1rem; border-radius:0.5rem;"><code>namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\AutoControllerGenerator;
use App\Helpers\Filter;
use App\Helpers\SchemaExtractor;

class UserController extends Controller {
    protected string $modelClass = User::class;

    public function __construct() {
        AutoControllerGenerator::init($this->modelClass);
        Filter::addTableFilter('users', 'remember_token');
    }

    public function create() { return AutoControllerGenerator::handleView(__FUNCTION__); }
    public function store(Request $request) { AutoControllerGenerator::handleRequest(__FUNCTION__, $request); return redirect()->route('users.create')->with('success', 'User created successfully.'); }
    public function edit($id) { return AutoControllerGenerator::handleView(__FUNCTION__, $id); }
    public function update(Request $request, $id) { AutoControllerGenerator::handleRequest(__FUNCTION__, $request, $id); return redirect()->route('users.edit', $id)->with('success', 'User updated successfully.'); }
    public function index() {
        $table = (new $this->modelClass)->getTable();
        $columns = Filter::filterColumns(SchemaExtractor::getTableColumns($table), $table);
        $data = $this->modelClass::select(array_column($columns, 'name'))->paginate(10);
        return view('autocontroller.index', compact('columns', 'data'));
    }
}</code></pre>

<h3>6. Blade Form Example</h3>
<pre style="background:#f3f4f6; padding:1rem; border-radius:0.5rem;"><code>{!! \App\Helpers\ColumnInputMapper::getCDN() !!}
<form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data">
    @csrf
    @foreach($columns as $column)
        <label>{{ ucfirst($column['name']) }}</label>
        {!! \App\Helpers\ColumnInputMapper::getInput($column, $values[$column['name']] ?? null) !!}
    @endforeach
    <button type="submit" style="background-color:#2563eb; color:white; padding:0.5rem 1rem; border-radius:0.5rem; margin-top:1rem;">Save</button>
</form></code></pre>

<h3>7. Input Styles Customization</h3>
<pre style="background:#f3f4f6; padding:1rem; border-radius:0.5rem;"><code>return match(env('CSS_FRAMEWORK','tailwind')) {
    'tailwind' => [
        'text' => '!block !w-full !rounded-xl !border !border-gray-200 !px-4 !py-2 !bg-white !placeholder-gray-400 !text-gray-800 !shadow-sm !transition-colors !duration-150 !focus:!outline-none !focus:!ring-4 !focus:!ring-blue-100 !focus:!border-blue-400',
        'number' => '...',
        'email' => '...',
        'password' => '...',
        'select' => '...',
        'textarea' => '...',
        'checkbox' => '...',
        'file' => '...',
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
};</code></pre>

<h3>8. How to Use in Projects</h3>
<ul>
    <li>Create migration</li>
    <li>Define model & <code>$fillable</code></li>
    <li>Create controller with AutoControllerGenerator</li>
    <li>Use <code>ColumnInputMapper::getInput()</code> in Blade</li>
    <li>Set <code>CSS_FRAMEWORK</code> in .env</li>
    <li>Optionally, add filters:
        <pre style="background:#f3f4f6; padding:1rem; border-radius:0.5rem;"><code>Filter::addTableFilter('users', 'remember_token');
Filter::addGlobalFilter('deleted_at');</code></pre>
    </li>
</ul>

<h3>9. Commands & Purpose</h3>
<table border="1" cellpadding="6" style="border-collapse:collapse;">
<tr><th>Command</th><th>Purpose</th></tr>
<tr><td>php artisan make:migration</td><td>Create a migration file</td></tr>
<tr><td>php artisan migrate</td><td>Run all migrations</td></tr>
<tr><td>php artisan make:model</td><td>Create an Eloquent model</td></tr>
<tr><td>php artisan make:controller</td><td>Create a controller class</td></tr>
<tr><td>Filter::addTableFilter($table, $column)</td><td>Hide column from auto-generated form</td></tr>
<tr><td>Filter::addGlobalFilter($column)</td><td>Hide column globally</td></tr>
<tr><td>AutoControllerGenerator::handleView($method, $id=null)</td><td>Generate create/edit form view</td></tr>
<tr><td>AutoControllerGenerator::handleRequest($method, $request, $id=null)</td><td>Handle store/update requests automatically</td></tr>
<tr><td>ColumnInputMapper::getInput($column, $value)</td><td>Return styled HTML input element</td></tr>
<tr><td>ColumnInputMapper::getCDN()</td><td>Return CDN for Tailwind/Bootstrap</td></tr>
</table>

<h3>10. Notes</h3>
<ul>
    <li>File uploads require <code>enctype="multipart/form-data"</code>.</li>
    <li>Enum/select values are auto-populated from DB or foreign keys.</li>
    <li>Tailwind classes use <code>!</code> for <code>!important</code> overrides.</li>
    <li>Compatible with Laravel 12+, PHP 8.2+.</li>
</ul>

<h3>11. Styling & Colors</h3>
<ul>
    <li>Text inputs: Blue focus ring</li>
    <li>Number inputs: Green focus ring</li>
    <li>Email inputs: Purple focus ring</li>
    <li>Password inputs: Red focus ring</li>
    <li>Textareas: Yellow focus ring</li>
    <li>Selects: Indigo focus ring</li>
    <li>Checkbox: Blue ring</li>
    <li>File: White background with shadow</li>
</ul>

<div style="text-align:center; margin-top:2rem;">
  <img src="https://raw.githubusercontent.com/shahrakii/laravel-autoform/main/demo-form.png" width="600" alt="Demo Form">
</div>

</body>
</html>
