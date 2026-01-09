@if ($requests->count() > 0)
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    @if ($show_actions)
                        <th><input type="checkbox" id="select-all"></th>
                    @endif
                    <th>S.No.</th>
                    <th>Employee</th>
                    <th>Date</th>
                    <th>Clock In</th>
                    <th>Clock Out</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Approver</th>
                    @if ($show_actions)
                        <th>Action</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($requests as $key => $request)

                    <tr>
                        @if ($show_actions)
                            <td><input type="checkbox" name="request_ids[]" value="{{ Crypt::encrypt($request->id) }}"></td>
                        @endif
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $request->employee->name ?? 'N/A' }}</td>
                        <td>{{ $request->date }}</td>
                        <td>{{ $request->check_in }}</td>
                        <td>{{ $request->check_out }}</td>
                        <td>{{ $request->reason }}</td>
                        <td><span
                                class="badge @if ($request->status == 'pending') bg-warning @elseif($request->status == 'approved') bg-success @else bg-danger @endif">{{ $request->status }}</span>
                        </td>
                        <td>{{ $request->approver->name ?? '' }}</td>
                        @if ($show_actions)
                            @if (isset($canEditRegularization) && $canEditRegularization)
                                <td>
                                    <a href="{{ route('regularization-requests.edit', Crypt::encrypt($request->id)) }}"
                                    class="btn btn-outline-primary btn-sm action-btn" title="Edit Request">
                                    <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            @else
                                <td></td>
                            @endif
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if(isset($pagination_name))
        {{ $requests->appends(['page_name' => $pagination_name])->links() }}
    @else
        {{ $requests->links() }}
    @endif
@else
    <div class="alert alert-info mt-3" role="alert">
        No regularization requests found.
    </div>
@endif
