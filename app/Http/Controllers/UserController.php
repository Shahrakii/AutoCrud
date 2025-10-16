<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TestInput;
use App\Helpers\AutoControllerGenerator;
use App\Helpers\Filter;
use App\Helpers\SchemaExtractor;

class UserController extends Controller
{
    protected string $modelClass = TestInput::class;

    public function __construct()
    {
        // Initialize auto controller generator for this model
        AutoControllerGenerator::init($this->modelClass);
    }

    public function create()
    {
        // Auto-generate create form
        return AutoControllerGenerator::handleView(__FUNCTION__);
    }

    public function store(Request $request)
    {
        // Validate & store automatically
        AutoControllerGenerator::handleRequest(__FUNCTION__, $request);

        return redirect()->route('testinputs.create')->with('success', 'Record created successfully.');
    }

    public function edit($id)
    {
        // Auto-generate edit form
        return AutoControllerGenerator::handleView(__FUNCTION__, $id);
    }

    public function update(Request $request, $id)
    {
        // Auto-validate & update
        AutoControllerGenerator::handleRequest(__FUNCTION__, $request, $id);

        return redirect()->route('testinputs.edit', $id)->with('success', 'Record updated successfully.');
    }

    public function index()
    {
        $table = (new $this->modelClass)->getTable();

        // Fetch filtered columns for listing
        $columns = Filter::filterColumns(SchemaExtractor::getTableColumns($table), $table);

        $data = $this->modelClass::select(array_column($columns, 'name'))->paginate(10);

        return view('autocontroller.index', [
            'columns' => $columns,
            'data'    => $data
        ]);
    }

    public function destroy($id)
    {
        $record = $this->modelClass::findOrFail($id);
        $record->delete();

        return redirect()->route('testinputs.index')->with('success', 'Record deleted successfully.');
    }
}
