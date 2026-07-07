<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\BehaviorIncident;
use App\Models\ClassSession;
use App\Models\Course;
use App\Models\DataLeakageIncident;
use App\Models\Payment;
use App\Models\Subscription;
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
            'active_courses'    => Course::where('status', 'active')->count(),
            'active_subscriptions' => Subscription::where('status', 'active')
                ->whereDate('end_date', '>=', today())
                ->count(),
            'pending_payments' => Payment::where('status', 'pending')->count(),
            'approved_revenue' => Payment::where('status', 'approved')->sum('amount'),
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
        $statusData = [
            'labels' => ['Scheduled', 'Live', 'Completed', 'Cancelled'],
            'values' => [
                ClassSession::where('status', 'scheduled')->count(),
                ClassSession::where('status', 'live')->count(),
                ClassSession::where('status', 'completed')->count(),
                ClassSession::where('status', 'cancelled')->count(),
            ],
        ];

        return view('admin.dashboard', compact(
            'stats', 'recentAlerts', 'recentIncidents', 'leakageIncidents', 'weeklyData', 'statusData'
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
