@extends('layouts.app')

@section('title', 'My Calendar')
@section('page-title', 'My Calendar')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-semibold mb-1">My Calendar</h4>
            <p class="text-muted small mb-0">Sessions and assignment deadlines for {{ $month->format('F Y') }}.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('student.calendar', ['month' => $month->copy()->subMonth()->format('Y-m')]) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-chevron-left"></i>
            </a>
            <a href="{{ route('student.calendar') }}" class="btn btn-sm btn-outline-primary">Today</a>
            <a href="{{ route('student.calendar', ['month' => $month->copy()->addMonth()->format('Y-m')]) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-chevron-right"></i>
            </a>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="calendar-grid border-bottom bg-light text-muted small fw-semibold">
            @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $dayName)
                <div class="p-3 border-end">{{ $dayName }}</div>
            @endforeach
        </div>

        <div class="calendar-grid">
            @foreach($calendarDays as $day)
                @php
                    $dateKey = $day->format('Y-m-d');
                    $dayEvents = $events->get($dateKey, collect());
                    $isCurrentMonth = $day->month === $month->month;
                    $isToday = $day->isToday();
                @endphp
                <div class="calendar-cell p-2 border-end border-bottom {{ $isCurrentMonth ? '' : 'bg-light text-muted' }}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold {{ $isToday ? 'badge bg-primary rounded-pill' : '' }}">{{ $day->day }}</span>
                        @if($dayEvents->isNotEmpty())
                            <span class="badge bg-light text-muted border">{{ $dayEvents->count() }}</span>
                        @endif
                    </div>

                    <div class="d-flex flex-column gap-1">
                        @foreach($dayEvents as $event)
                            <a href="{{ $event['url'] }}" class="calendar-event text-decoration-none {{ $event['type'] === 'session' ? 'event-session' : 'event-assignment' }}">
                                <span class="d-block text-truncate fw-medium">{{ $event['date']->format('H:i') }} {{ $event['title'] }}</span>
                                <span class="d-block text-truncate opacity-75">{{ $event['subtitle'] }} · {{ ucfirst($event['status']) }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@push('styles')
<style>
.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
}

.calendar-cell {
    min-height: 132px;
}

.calendar-event {
    border-radius: .45rem;
    padding: .35rem .45rem;
    font-size: .72rem;
    line-height: 1.2;
}

.event-session {
    background: #eef2ff;
    color: #3730a3;
}

.event-assignment {
    background: #fff7ed;
    color: #9a3412;
}

@media (max-width: 768px) {
    .calendar-grid {
        grid-template-columns: 1fr;
    }

    .calendar-grid.border-bottom {
        display: none;
    }
}
</style>
@endpush
