@foreach($logs as $log)
    <tr class="border-b last:border-none">
        <td class="py-3 px-6">{{ $loop->iteration }}</td>
        <td class="py-3 px-6">{{ $log->created_at->format('M d, H:i') }}</td>
        <td class="py-3 px-6">
            {{ str_limit($log->actor->name ?? $log->actor_type, 20) }}
        </td>
        <td class="py-3 px-6">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</td>
        <td class="py-3 px-6">
            {{ str_limit($log->subject_type ?? 'N/A', 30) }}
        </td>
        <td class="py-3 px-6">{{ $log->ip_address ?? 'N/A' }}</td>
        <td class="py-3 px-6">
            {{ str_limit($log->reason ?? 'N/A', 50) }}
        </td>
    </tr>
@endforeach