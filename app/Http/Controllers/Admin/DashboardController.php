<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\BehaviorIncident;
use App\Models\ClassSession;
use App\Models\DataLeakageIncident;
use App\Models\DeadAirLog;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_instructors' => User::where('role', 'instructor')->count(),
            'total_students'    => User::where('role', 'student')->count(),
            'total_sessions'    => ClassSession::count(),
            'live_sessions'     => ClassSession::where('status', 'live')->count(),
            'unread_alerts'     => Alert::where('is_read', false)->count(),
            'today_sessions'    => ClassSession::whereDate('scheduled_at', today())->count(),
        ];

        $recentAlerts = Alert::with(['targetUser', 'classSession'])
            ->where('is_read', false)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $recentIncidents = BehaviorIncident::with(['user', 'classSession'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $leakageIncidents = DataLeakageIncident::with(['user', 'classSession'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $weeklyData = $this->getWeeklyChartData();

        return view('admin.dashboard', compact(
            'stats', 'recentAlerts', 'recentIncidents', 'leakageIncidents', 'weeklyData'
        ));
    }

    private function getWeeklyChartData(): array
    {
        $labels   = [];
        $sessions = [];
        $alerts   = [];

        for ($i = 6; $i >= 0; $i--) {
            $date     = Carbon::now()->subDays($i);
            $labels[] = $date->format('D');
            $sessions[] = ClassSession::whereDate('scheduled_at', $date)->count();
            $alerts[]   = Alert::whereDate('created_at', $date)->count();
        }

        return compact('labels', 'sessions', 'alerts');
    }
}
