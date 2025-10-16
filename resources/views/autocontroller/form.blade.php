<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Form</title>
    {!! \App\Helpers\ColumnInputMapper::getCDN() !!}
</head>
<body class="bg-gray-50 p-8">

<form method="POST" action="{{ isset($values['id']) ? route('users.update', $values['id']) : route('users.store') }}" enctype="multipart/form-data" class="max-w-xl mx-auto bg-white p-6 rounded-xl shadow-lg space-y-4">
    @csrf
    @if(isset($values['id']))
        @method('PUT')
    @endif

    @foreach($columns as $column)
        <div>
            <label class="block mb-1 font-semibold text-gray-700">{{ ucfirst($column['name']) }}</label>
            {!! \App\Helpers\ColumnInputMapper::getInput($column, $values[$column['name']] ?? null) !!}
        </div>
    @endforeach

    <button type="submit" class="!bg-blue-500 !text-white !px-6 !py-2 !rounded-lg hover:!bg-blue-600 transition-colors duration-200">Save</button>
</form>

</body>
</html>
