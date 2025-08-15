@extends('agent.layouts.master')

@section('title','Agent Dashboard')
@section('page_title','Dashboard')

@section('content')
<div class="row g-3">

    {{-- Stats Cards --}}
    <div class="col-12 col-md-3">
        <div class="card shadow-sm stat-card">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted">Total Chats</div>
                    <div class="h3 m-0">{{ $totalChats }}</div>
                </div>
                <i class="bi bi-chat-dots icon"></i>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-3">
        <div class="card shadow-sm stat-card">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted">Today</div>
                    <div class="h3 m-0">{{ $todayChats }}</div>
                </div>
                <i class="bi bi-calendar-day icon"></i>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-3">
        <div class="card shadow-sm stat-card">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted">Open</div>
                    <div class="h3 m-0">{{ $openChats }}</div>
                </div>
                <i class="bi bi-folder2-open icon"></i>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-3">
        <div class="card shadow-sm stat-card">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted">Closed</div>
                    <div class="h3 m-0">{{ $closedChats }}</div>
                </div>
                <i class="bi bi-check2-circle icon"></i>
            </div>
        </div>
    </div>

    {{-- Chart --}}
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <strong>Chats (Last 7 Days)</strong>
            </div>
            <div class="card-body">
                <canvas id="chatsChart" height="90"></canvas>
            </div>
        </div>
    </div>

    {{-- Search + Table --}}
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex flex-wrap gap-2 align-items-center">
                <strong class="me-auto">Recent Chats</strong>
                <input type="text" class="form-control" id="chatSearch" placeholder="Search by customer..." style="max-width: 260px;">
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0" id="chatsTable">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Updated</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($chats as $chat)
                            <tr>
                                <td>#{{ $chat->id }}</td>
                                <td>{{ $chat->customer->name ?? 'N/A' }}</td>
                                <td>
                                    @php $status = $chat->status ?? 'open'; @endphp
                                    <span class="badge bg-{{ $status === 'closed' ? 'success' : 'primary' }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td>{{ $chat->updated_at?->diffForHumans() }}</td>
                                <td class="text-end">
                                    <a href="{{ route('chat.show', ['chatId' => $chat->id]) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-arrow-right-circle"></i> Open
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No chats found.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@endpush

@push('scripts')
<script>
(function(){
    // Chart
    const labels = @json($dataset->pluck('label'));
    const counts = @json($dataset->pluck('count'));

    const ctx = document.getElementById('chatsChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Chats',
                    data: counts,
                    tension: .35,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false }},
                scales: { y: { beginAtZero: true, ticks: { precision:0 } } }
            }
        });
    }

    // Table search
    const searchInput = document.getElementById('chatSearch');
    const rows = Array.from(document.querySelectorAll('#chatsTable tbody tr'));
    searchInput?.addEventListener('input', (e) => {
        const q = e.target.value.toLowerCase();
        rows.forEach(tr => {
            const customer = (tr.children[1]?.innerText || '').toLowerCase();
            tr.style.display = customer.includes(q) ? '' : 'none';
        });
    });
})();
</script>
@endpush
