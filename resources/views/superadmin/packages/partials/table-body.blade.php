<tbody id="packages-tbody">
    @forelse($packages ?? [] as $package)
        <tr data-package-id="{{ $package->id }}">
            <td>{{ $package->name }}</td>
            <td>{{ ucfirst(str_replace('_', '-', $package->pricing_type)) }}</td>
            <td>{{ $package->currency }} {{ number_format($package->base_price, 2) }}</td>
            <td>{{ $package->billing_cycle ? ucfirst($package->billing_cycle) : '-' }}</td>
            <td>
                <span class="badge badge-{{ $package->is_active ? 'success' : 'danger' }}">
                    {{ $package->is_active ? 'Active' : 'Inactive' }}
                </span>
            </td>
            <td>
                <a href="{{ route('superadmin.packages.show', $package) }}" class="btn btn-sm btn-info">
                    <i class="fas fa-eye"></i> View
                </a>
                <a href="{{ route('superadmin.packages.edit', $package) }}" class="btn btn-sm btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <form action="{{ route('superadmin.packages.toggle-active', $package) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-sm btn-{{ $package->is_active ? 'danger' : 'success' }}">
                        <i class="fas fa-{{ $package->is_active ? 'times' : 'check' }}"></i>
                        {{ $package->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                </form>
            </td>
        </tr>
    @empty
        <tr id="no-packages-row">
            <td colspan="6" class="text-center">No packages found.</td>
        </tr>
    @endforelse
</tbody>