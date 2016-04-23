<table border="1">
    <tr>
        <td>ProHisId</td>
        <td>Code</td>
        <td>Status</td>
        <td>Label</td>
        <td>Name</td>
        <td>Answer</td>
    </tr>


    @foreach($data as $task)
        @foreach($task->jsonGet('items', [], ['local']) as $item)
            <tr>
                <td>{{ $task->process_history_id }}</td>
                <td>{{ $task->code }}</td>
                <td>{{ $task->status }}</td>
                <td>{{ $task->jsonGet('label', null, ['local']) }}</td>
                <td>{{ array_get($item, 'label') }}</td>
                <td>{{ array_get($item, 'answer') }}</td>
            </tr>
        @endforeach
    @endforeach
</table>