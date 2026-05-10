<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSession;
use App\Models\Report;
use App\Models\User;
use App\Services\ReportGeneratorService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private ReportGeneratorService $reportGenerator) {}

    public function index(Request $request)
    {
        $query = Report::with('generatedBy', 'subjectUser');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $reports = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('admin.reports.index', compact('reports'));
    }

    public function show(Report $report)
    {
        return view('admin.reports.show', compact('report'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'type'       => 'required|in:daily,weekly,monthly,instructor,session',
            'date'       => 'nullable|date',
            'instructor' => 'nullable|exists:users,id',
            'session'    => 'nullable|exists:class_sessions,id',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        $user = auth()->user();

        $report = match($request->type) {
            'daily'    => $this->reportGenerator->generateDailyReport($user, Carbon::parse($request->date ?? now())),
            'weekly'   => $this->reportGenerator->generateWeeklyReport($user),
            'monthly'  => $this->reportGenerator->generateMonthlyReport($user),
            'instructor' => $this->reportGenerator->generateInstructorReport(
                User::findOrFail($request->instructor),
                $user,
                Carbon::parse($request->start_date ?? now()->startOfMonth()),
                Carbon::parse($request->end_date ?? now())
            ),
            'session'  => $this->reportGenerator->generateSessionReport(
                ClassSession::findOrFail($request->session),
                $user
            ),
        };

        return redirect()->route('admin.reports.show', $report)->with('success', 'Report generated.');
    }

    public function generateForm()
    {
        $instructors = User::where('role', 'instructor')->get();
        $sessions    = ClassSession::orderByDesc('scheduled_at')->limit(50)->get();
        return view('admin.reports.generate', compact('instructors', 'sessions'));
    }
}
