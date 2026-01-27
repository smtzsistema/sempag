<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;

class DashboardController extends Controller
{
    public function index(Event $event)
    {
        $u = auth()->user();

        // Se não puder ver dashboard, manda pra um lugar que ele consiga (opcional, mas faz sentido)
        if ($u && !$u->can('dashboard.view')) {
            if ($u->can('registrations.view')) {
                return redirect()->route('admin.registrations.index', $event);
            }
            // fallback: se não tiver outra permissão, deixa cair no dashboard mesmo (mas com dados)
        }

        // Base: não contar "excluídos" (N)
        $base = Registration::query()
            ->where('eve_id', $event->id)
            ->where('ins_aprovado', '!=', 'N');

        // Status oficiais: S/E/R/N
        $counts = [
            'total' => (clone $base)->count(),
            'pending' => (clone $base)->where('ins_aprovado', 'E')->count(),
            'approved' => (clone $base)->where('ins_aprovado', 'S')->count(),
            'rejected' => (clone $base)->where('ins_aprovado', 'R')->count(),
        ];

        $countsByCategory = Registration::query()
            ->where('eve_id', $event->id)
            ->where('ins_aprovado', '!=', 'N')
            ->select('cat_id')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN ins_aprovado = 'E' THEN 1 ELSE 0 END) as pending")
            ->selectRaw("SUM(CASE WHEN ins_aprovado = 'S' THEN 1 ELSE 0 END) as approved")
            ->selectRaw("SUM(CASE WHEN ins_aprovado = 'R' THEN 1 ELSE 0 END) as rejected")
            ->with('category')
            ->groupBy('cat_id')
            ->orderByDesc('total')
            ->get();

        $latest = Registration::query()
            ->where('eve_id', $event->id)
            ->where('ins_aprovado', '!=', 'N')
            ->with('category')
            ->orderByDesc('ins_id')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('event', 'counts', 'countsByCategory', 'latest'));
    }
}
