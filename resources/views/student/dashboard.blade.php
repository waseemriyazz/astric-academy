@extends('layouts.student')

@section('header', 'Dashboard')

@section('content')
@php
    /*
    |--------------------------------------------------------------------------
    | Derived from the variables the controller ALREADY passes in:
    | $totalCourses, $completedLessons, $totalLessons, $overallProgress, $courses
    |--------------------------------------------------------------------------
    */
    $remainingLessons = max((int) $totalLessons - (int) $completedLessons, 0);
    $noLessonsYet     = ((int) $totalLessons) === 0;

    // Radial data — falls back to an empty ring when the courses have no lessons.
    $radialData = $noLessonsYet ? [0, 1] : [(int) $completedLessons, $remainingLessons];

    $chartLabels     = [];
    $chartValues     = [];
    $notStartedCount = 0;
    $inProgressCount = 0;
    $finishedCount   = 0;

    foreach ($courses as $stat) {
        $p = (int) ($stat->progress_percentage ?? 0);

        $chartLabels[] = \Illuminate\Support\Str::limit($stat->title, 26);
        $chartValues[] = $p;

        if ($p >= 100) {
            $finishedCount++;
        } elseif ($p > 0) {
            $inProgressCount++;
        } else {
            $notStartedCount++;
        }
    }

    // Keeps the bar chart readable whether the student has 1 course or 20.
    $barChartHeight = max(220, min(count($chartValues) * 46 + 20, 460));
@endphp

{{-- ==========================================================================
     ROW 1 — NUMBER CARDS
     ========================================================================== --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
    {{-- Courses enrolled --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-all duration-300">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-brand-500 to-brand-700 rounded-2xl flex items-center justify-center shadow-lg shadow-brand-200">
                    <i class="fas fa-book-open text-2xl text-white"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Courses Enrolled</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalCourses }}</p>
                </div>
            </div>
            <div class="px-3 py-1.5 bg-brand-50 text-brand-600 rounded-full text-xs font-bold">
                Active
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="flex items-center justify-between text-xs text-gray-400">
                <span><i class="fas fa-graduation-cap mr-1"></i> Total enrolled courses</span>
                <span class="text-brand-600 font-semibold">{{ $totalCourses }} course{{ $totalCourses !== 1 ? 's' : '' }}</span>
            </div>
        </div>
    </div>

    {{-- Lessons completed --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-all duration-300">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-200">
                    <i class="fas fa-check-circle text-2xl text-white"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Lessons Completed</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $completedLessons }} <span class="text-lg text-gray-400">/ {{ $totalLessons }}</span></p>
                </div>
            </div>
            <div class="px-3 py-1.5 bg-emerald-50 text-emerald-600 rounded-full text-xs font-bold">
                {{ $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0 }}%
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="flex items-center justify-between text-xs text-gray-400">
                <span><i class="fas fa-check-double mr-1"></i> Lessons completed</span>
                <span class="text-emerald-600 font-semibold">{{ $completedLessons }}/{{ $totalLessons }} done</span>
            </div>
        </div>
    </div>
</div>

@if($courses->isNotEmpty())
{{-- ==========================================================================
     ROW 2 — ONE ANALYTICS CARD (shadcn/ui design tokens + Card anatomy)
     Overall Progress + Progress by Course are now a single card:
       CardHeader  -> title + stat cells (not started / in progress / completed)
       CardContent -> radial chart | horizontal bar chart
       CardFooter  -> summary line
     ========================================================================== --}}
<style>
    /* shadcn/ui tokens, scoped to this card so nothing leaks into the app */
    .sc-analytics {
        --card: 0 0% 100%;
        --card-foreground: 222.2 84% 4.9%;
        --muted: 220 14% 96%;
        --muted-foreground: 215.4 16.3% 46.9%;
        --border: 220 13% 91%;
        --radius: 1rem;

        --chart-1: 199 91% 50%;   /* brand blue — in progress */
        --chart-2: 258 90% 66%;   /* violet  — accent      */
        --chart-3: 160 84% 39%;   /* emerald — completed   */
        --chart-4: 220 13% 91%;   /* grey    — not started */

        background-color: hsl(var(--card));
        color: hsl(var(--card-foreground));
        border: 1px solid hsl(var(--border));
        border-radius: var(--radius);
        box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    }

    .sc-border        { border-color: hsl(var(--border)); }
    .sc-muted         { color: hsl(var(--muted-foreground)); }
    .sc-title         { font-size: 1.125rem; font-weight: 700; letter-spacing: -0.01em; line-height: 1.25; }
    .sc-description   { font-size: 0.8125rem; line-height: 1.35; color: hsl(var(--muted-foreground)); }
    .sc-stat-label    { font-size: 0.75rem; font-weight: 500; color: hsl(var(--muted-foreground)); }
    .sc-stat-value    { font-size: 1.5rem; font-weight: 700; line-height: 1; font-variant-numeric: tabular-nums; }
    .sc-text-progress { color: hsl(var(--chart-1)); }
    .sc-text-complete { color: hsl(var(--chart-3)); }

    .sc-swatch          { width: 0.625rem; height: 0.625rem; border-radius: 3px; display: inline-block; flex: none; }
    .sc-swatch-progress { background-color: hsl(var(--chart-1)); }
    .sc-swatch-complete { background-color: hsl(var(--chart-3)); }
    .sc-swatch-idle     { background-color: hsl(var(--chart-4)); }
    .sc-swatch-track    { background-color: hsl(var(--muted)); }

    /* ChartTooltipContent look-alike */
    .sc-tooltip {
        position: absolute;
        z-index: 20;
        min-width: 9rem;
        padding: 0.5rem 0.625rem;
        border: 1px solid hsl(var(--border));
        border-radius: calc(var(--radius) - 0.5rem);
        background-color: hsl(var(--card));
        box-shadow: 0 8px 20px -6px rgb(16 24 40 / 0.14), 0 2px 6px -2px rgb(16 24 40 / 0.06);
        font-size: 0.75rem;
        line-height: 1.2;
        opacity: 0;
        pointer-events: none;
        transform: translate(-50%, -118%);
        transition: opacity 0.12s ease-out;
    }
    .sc-tooltip-label { font-weight: 600; margin-bottom: 0.4rem; color: hsl(var(--card-foreground)); }
    .sc-tooltip-row   { display: flex; align-items: center; gap: 0.5rem; }
    .sc-tooltip-name  { color: hsl(var(--muted-foreground)); }
    .sc-tooltip-value { margin-left: auto; font-weight: 600; font-variant-numeric: tabular-nums; }
</style>

<div class="sc-analytics mb-8">

    {{-- CardHeader: title + statistics cells --}}
    <div class="flex flex-col sm:flex-row">
        <div class="flex flex-1 flex-col justify-center gap-1 px-6 py-5">
            <h3 class="sc-title">Learning Progress</h3>
            <p class="sc-description">Overall completion and a course-by-course breakdown</p>
        </div>

        <div class="flex">
            <div class="flex flex-1 flex-col justify-center gap-1.5 border-t sc-border px-6 py-4 sm:border-l sm:border-t-0 sm:py-5">
                <span class="sc-stat-label">Not started</span>
                <span class="sc-stat-value">{{ $notStartedCount }}</span>
            </div>
            <div class="flex flex-1 flex-col justify-center gap-1.5 border-t border-l sc-border px-6 py-4 sm:border-t-0 sm:py-5">
                <span class="sc-stat-label">In progress</span>
                <span class="sc-stat-value sc-text-progress">{{ $inProgressCount }}</span>
            </div>
            <div class="flex flex-1 flex-col justify-center gap-1.5 border-t border-l sc-border px-6 py-4 sm:border-t-0 sm:py-5">
                <span class="sc-stat-label">Completed</span>
                <span class="sc-stat-value sc-text-complete">{{ $finishedCount }}</span>
            </div>
        </div>
    </div>

    {{-- CardContent: radial + bars, side by side --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 border-t sc-border">

        {{-- Radial: overall completion --}}
        <div class="lg:col-span-1 flex flex-col items-center justify-center gap-5 px-6 py-8">
            <div class="relative" style="width: 190px; height: 190px;">
                <canvas id="scProgressRadial"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span class="text-4xl font-bold tracking-tight tabular-nums">{{ $overallProgress }}%</span>
                    <span class="sc-description mt-1">Complete</span>
                </div>
            </div>

            <div class="grid w-full grid-cols-2 gap-3">
                <div class="rounded-2xl border sc-border px-3 py-2.5">
                    <div class="flex items-center gap-2 sc-stat-label">
                        <span class="sc-swatch sc-swatch-progress"></span> Done
                    </div>
                    <p class="mt-1.5 text-lg font-bold tabular-nums">{{ $completedLessons }} <span class="text-xs font-medium sc-muted">lessons</span></p>
                </div>
                <div class="rounded-2xl border sc-border px-3 py-2.5">
                    <div class="flex items-center gap-2 sc-stat-label">
                        <span class="sc-swatch sc-swatch-track"></span> Left
                    </div>
                    <p class="mt-1.5 text-lg font-bold tabular-nums">{{ $remainingLessons }} <span class="text-xs font-medium sc-muted">lessons</span></p>
                </div>
            </div>
        </div>

        {{-- Bars: progress per course --}}
        <div class="lg:col-span-2 border-t lg:border-t-0 lg:border-l sc-border px-6 py-6">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                <p class="text-sm font-semibold">Progress by course</p>
                <div class="flex items-center gap-4 text-xs sc-muted">
                    <span class="flex items-center gap-1.5"><span class="sc-swatch sc-swatch-complete"></span> Completed</span>
                    <span class="flex items-center gap-1.5"><span class="sc-swatch sc-swatch-progress"></span> In progress</span>
                    <span class="flex items-center gap-1.5"><span class="sc-swatch sc-swatch-idle"></span> Not started</span>
                </div>
            </div>

            <div class="relative w-full" style="height: {{ $barChartHeight }}px;">
                <canvas id="scCourseBars"></canvas>
            </div>
        </div>
    </div>

    {{-- CardFooter --}}
    <div class="flex flex-wrap items-center justify-between gap-2 border-t sc-border px-6 py-4 text-sm">
        <span class="font-medium">
            <i class="fas fa-circle-check sc-text-complete mr-1.5"></i>
            {{ $completedLessons }} of {{ $totalLessons }} lessons complete
        </span>
        <span class="sc-muted">{{ $remainingLessons }} lesson{{ $remainingLessons !== 1 ? 's' : '' }} left across {{ $totalCourses }} course{{ $totalCourses !== 1 ? 's' : '' }}</span>
    </div>
</div>
@endif

{{-- ==========================================================================
     COURSES
     ========================================================================== --}}
<div class="mb-6">
    <h3 class="text-xl font-bold text-gray-900 mb-2">My Courses</h3>
    <p class="text-gray-500 text-base">Continue learning from where you left off. Track your progress across all enrolled courses.</p>
</div>

@if($courses->isEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-12 text-center max-w-3xl mx-auto mt-12">
        <div class="w-24 h-24 bg-brand-50 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-box-open text-4xl text-brand-300"></i>
        </div>
        <h3 class="text-2xl font-bold text-gray-900 mb-2">No active enrollments yet</h3>
        <p class="text-gray-500 mb-8 max-w-md mx-auto">You haven't been enrolled in any courses yet. When an administrator grants you access, your courses will appear here.</p>
        <a href="https://skillstryx.com/#courses" class="inline-flex items-center gap-2 px-6 py-3 bg-brand-600 text-white font-semibold rounded-2xl hover:bg-brand-700 transition shadow-sm">
            <span>Explore Course Catalog</span>
            <i class="fas fa-arrow-right text-sm"></i>
        </a>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($courses as $course)
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md border border-gray-200 overflow-hidden transition-all duration-300 flex flex-col group">
            {{-- Header with gradient --}}
            <div class="h-48 bg-gradient-to-br from-brand-600 to-cyan-500 relative overflow-hidden flex-shrink-0 flex items-center justify-center p-6 text-center">
                <div class="absolute inset-0 bg-black/10"></div>
                <div class="absolute -right-10 -top-10 w-32 h-32 rounded-full bg-white/10 blur-2xl"></div>

                {{-- Price badge — top-left corner --}}
                <div class="absolute top-4 left-4 z-10">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold shadow-sm
                        {{ !is_null($course->price_max) && $course->price_max > 0 ? 'bg-white/90 backdrop-blur text-brand-700' : 'bg-emerald-500/90 backdrop-blur text-white' }}">
                        <i class="fas fa-tag text-[10px]"></i>
                        @if(!is_null($course->price_max) && $course->price_max > 0)
                            ${{ number_format($course->price_max, 2) }}
                        @else
                            Free
                        @endif
                    </span>
                </div>

                {{-- Status badge — top-right corner --}}
                <div class="absolute top-4 right-4 z-10">
                    @php
                        $pct = $course->progress_percentage;
                    @endphp
                    @if($pct >= 100)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider shadow-sm bg-emerald-500/90 backdrop-blur text-white">
                            <i class="fas fa-check-circle text-[10px]"></i>
                            Completed
                        </span>
                    @elseif($pct > 0)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider shadow-sm bg-white/90 backdrop-blur text-emerald-600">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                            In Progress
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider shadow-sm bg-white/80 backdrop-blur text-gray-500">
                            <i class="fas fa-clock text-[10px]"></i>
                            Not Started
                        </span>
                    @endif
                </div>

                <h3 class="text-white text-2xl font-bold relative z-10 leading-tight drop-shadow-md">
                    {{ $course->title }}
                </h3>
            </div>

            {{-- Course details --}}
            <div class="p-6 flex-1 flex flex-col">
                {{-- Tools badge --}}
                @if($course->tools_count > 0)
                    <div class="mb-2.5">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-brand-50 text-brand-700 text-[10px] font-bold uppercase tracking-widest border border-brand-100">
                            <i class="fas fa-tools"></i> {{ $course->tools_count }} Tools Program
                        </span>
                    </div>
                @endif

                {{-- Description — fixed height for consistency --}}
                <p class="text-gray-600 text-sm leading-relaxed h-[2.8rem] line-clamp-2 mb-4">
                    {{ $course->description ?? 'Start mastering the skills required for ' . $course->title . ' with our comprehensive curriculum.' }}
                </p>

                {{-- Meta row: lessons + tools count --}}
                <div class="flex items-center gap-4 mb-5">
                    <div class="flex items-center gap-1.5 text-xs text-gray-500">
                        <i class="fas fa-list-ul text-brand-400"></i>
                        <span class="font-medium">{{ $course->lessons->count() }} lesson{{ $course->lessons->count() !== 1 ? 's' : '' }}</span>
                    </div>
                    @if($course->tools_count > 0)
                        <div class="flex items-center gap-1.5 text-xs text-gray-500">
                            <i class="fas fa-tools text-brand-400"></i>
                            <span class="font-medium">{{ $course->tools_count }} tool{{ $course->tools_count !== 1 ? 's' : '' }}</span>
                        </div>
                    @endif
                    <div class="flex items-center gap-1.5 text-xs text-gray-500 ml-auto">
                        <i class="fas fa-check-double text-emerald-400"></i>
                        <span class="font-medium">{{ $course->completed_lessons }} done</span>
                    </div>
                </div>

                {{-- Progress --}}
                <div class="mt-auto mb-5">
                    <div class="flex items-center justify-between text-xs font-semibold mb-2">
                        <span class="text-gray-400">Course Progress</span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full {{ $pct == 100 ? 'bg-emerald-50 text-emerald-600' : ($pct > 0 ? 'bg-brand-50 text-brand-600' : 'bg-gray-50 text-gray-400') }}">
                            <i class="fas {{ $pct == 100 ? 'fa-check-circle' : ($pct > 0 ? 'fa-arrow-up' : 'fa-hourglass-start') }} text-[10px]"></i>
                            {{ $pct }}%
                        </span>
                    </div>

                    <div class="w-full h-2.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-brand-500 to-cyan-400 rounded-full transition-all duration-700 ease-out" style="width: {{ $pct }}%"></div>
                    </div>

                    <div class="flex items-center justify-between mt-1.5">
                        <div class="text-[11px] text-gray-400 font-medium">
                            @if($pct < 25)
                                Just Started
                            @elseif($pct < 50)
                                In Progress
                            @elseif($pct < 75)
                                Almost There
                            @elseif($pct < 100)
                                Nearly Done
                            @else
                                Completed!
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Action --}}
                <a href="{{ route('student.courses.show', $course->id) }}" class="block w-full text-center py-2.5 px-4 bg-gray-50 hover:bg-brand-600 text-gray-700 hover:text-white font-semibold rounded-2xl transition-all duration-300 border border-gray-200 hover:border-brand-600 flex items-center justify-center gap-2 text-sm">
                    @if($pct == 100)
                        <i class="fas fa-redo"></i> Review Course
                    @elseif($pct > 0)
                        <i class="fas fa-play"></i> Continue Learning
                    @else
                        <i class="fas fa-play"></i> Start Course
                    @endif
                </a>
            </div>
        </div>
        @endforeach
    </div>
@endif

{{-- ==========================================================================
     CHARTS — Chart.js styled with the shadcn tokens defined above.
     If layouts.student defines a scripts stack before the closing body tag,
     move these two script tags into that push block instead.
     ========================================================================== --}}
@if($courses->isNotEmpty())
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;

    var scope = document.querySelector('.sc-analytics');
    if (!scope) return;

    // Read the shadcn tokens so the charts and the card never drift apart.
    var token = function (name) {
        return 'hsl(' + getComputedStyle(scope).getPropertyValue(name).trim() + ')';
    };

    var COLOR = {
        inProgress: token('--chart-1'),
        completed:  token('--chart-3'),
        notStarted: token('--chart-4'),
        track:      token('--muted'),
        border:     token('--border'),
        muted:      token('--muted-foreground'),
    };

    Chart.defaults.font.family = "'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.color = COLOR.muted;

    var esc = function (value) {
        var node = document.createElement('div');
        node.textContent = String(value);
        return node.innerHTML;
    };

    // ChartTooltipContent look-alike (Chart.js external tooltip)
    function scTooltip(context) {
        var chart = context.chart;
        var model = context.tooltip;
        var parent = chart.canvas.parentNode;
        var el = parent.querySelector('.sc-tooltip');

        if (!el) {
            el = document.createElement('div');
            el.className = 'sc-tooltip';
            parent.appendChild(el);
        }

        if (model.opacity === 0 || !model.dataPoints || !model.dataPoints.length) {
            el.style.opacity = 0;
            return;
        }

        var point = model.dataPoints[0];
        var fills = point.dataset.backgroundColor;
        var color = Array.isArray(fills) ? fills[point.dataIndex] : fills;
        var value = typeof point.dataset.scFormat === 'function'
            ? point.dataset.scFormat(point)
            : point.formattedValue;

        el.innerHTML =
            '<div class="sc-tooltip-label">' + esc(point.label) + '</div>' +
            '<div class="sc-tooltip-row">' +
                '<span class="sc-swatch" style="background-color:' + esc(color) + '"></span>' +
                '<span class="sc-tooltip-name">' + esc(point.dataset.label || '') + '</span>' +
                '<span class="sc-tooltip-value">' + esc(value) + '</span>' +
            '</div>';

        el.style.opacity = 1;
        el.style.left = model.caretX + 'px';
        el.style.top = model.caretY + 'px';
    }

    /* ---------------- Radial: overall completion ---------------- */
    var radialEl = document.getElementById('scProgressRadial');

    if (radialEl) {
        var hasLessons = {{ $noLessonsYet ? 'false' : 'true' }};

        new Chart(radialEl.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'Remaining'],
                datasets: [{
                    label: 'Lessons',
                    data: @json($radialData),
                    backgroundColor: [COLOR.inProgress, COLOR.track],
                    borderWidth: 0,
                    hoverOffset: 2,
                    borderRadius: function (ctx) { return ctx.dataIndex === 0 ? 12 : 0; },
                    scFormat: function (point) {
                        var n = point.parsed;
                        return n + (n === 1 ? ' lesson' : ' lessons');
                    },
                }],
            },
            options: {
                cutout: '74%',
                responsive: true,
                maintainAspectRatio: false,
                animation: { animateRotate: true, duration: 800 },
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false, external: hasLessons ? scTooltip : function () {}, position: 'nearest' },
                },
            },
        });
    }

    /* ---------------- Bars: progress per course ---------------- */
    var barsEl = document.getElementById('scCourseBars');

    if (barsEl) {
        var labels = @json($chartLabels);
        var values = @json($chartValues);

        new Chart(barsEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Progress',
                    data: values,
                    backgroundColor: values.map(function (v) {
                        if (v >= 100) return COLOR.completed;
                        if (v > 0) return COLOR.inProgress;
                        return COLOR.notStarted;
                    }),
                    borderRadius: 6,
                    borderSkipped: false,
                    maxBarThickness: 16,
                    scFormat: function (point) { return point.parsed.x + '% complete'; },
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 800 },
                layout: { padding: { right: 4 } },
                scales: {
                    x: {
                        beginAtZero: true,
                        max: 100,
                        border: { display: false },
                        grid: { color: COLOR.border, drawTicks: false },
                        ticks: {
                            padding: 8,
                            stepSize: 25,
                            callback: function (value) { return value + '%'; },
                        },
                    },
                    y: {
                        border: { display: false },
                        grid: { display: false },
                        ticks: { padding: 8, crossAlign: 'far', color: COLOR.muted, font: { size: 12, weight: '500' } },
                    },
                },
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false, external: scTooltip, position: 'nearest' },
                },
            },
        });
    }
});
</script>
@endif
@endsection
