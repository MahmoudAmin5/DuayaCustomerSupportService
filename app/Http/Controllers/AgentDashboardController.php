<?php
// app/Http/Controllers/AgentDashboardController.php
namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Support\Facades\Auth;

class AgentDashboardController extends Controller
{
    public function index()
    {
        $agentId = Auth::guard('agent')->id();

        $chats = Chat::with(['customer'])
            ->where('agent_id', $agentId)
            ->latest()
            ->get();

        // Simple stats for cards & chart
        $totalChats = $chats->count();
        $todayChats = $chats->where('created_at', '>=', now()->startOfDay())->count();
        $openChats  = $chats->where('is_active', true)->count() ?? 0;
        $closedChats = $chats->where('is_active', false)->count() ?? 0;

        // Chart data (last 7 days)
        $byDay = collect(range(6, 0))->map(function ($i) {
            $date = now()->subDays($i)->startOfDay();
            return [
                'label' => $date->format('M d'),
                'date'  => $date->toDateString(),
            ];
        })->values();

        $dataset = $byDay->map(function ($d) use ($chats) {
            $count = $chats->filter(function ($c) use ($d) {
                return $c->created_at->toDateString() === $d['date'];
            })->count();
            return ['label' => $d['label'], 'count' => $count];
        });

        return view('agent.dashboard', compact(
            'chats',
            'totalChats',
            'todayChats',
            'openChats',
            'closedChats',
            'dataset'
        ));
    }
}
