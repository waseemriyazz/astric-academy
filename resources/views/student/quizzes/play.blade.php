@extends('layouts.student')

@section('header')
<div class="flex items-center gap-3">
    <a href="{{ route('student.courses.show', [$course->id, $lesson->id]) }}" class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-brand-600 hover:border-brand-200 transition shadow-sm">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="text-xl font-bold text-gray-900 leading-tight">Quick Quiz</h1>
        <p class="text-xs text-gray-500">{{ $lesson->course->title }}</p>
    </div>
</div>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
        <!-- Quiz Header -->
        <div class="bg-gradient-to-r from-cyan-500 to-brand-600 p-6 text-white">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center">
                    <i class="fas fa-question-circle text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold mb-1">Lesson Quiz</h2>
                    <p class="text-sm text-purple-200">{{ $lesson->title }}</p>
                </div>
            </div>
        </div>

        <!-- Quiz Body -->
        <div class="p-8">
            <div class="mb-6">
                <div class="flex items-center gap-2 mb-4">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-purple-50 text-purple-600 text-xs font-semibold border border-purple-200">
                        <i class="fas fa-bolt"></i> Quick Play
                    </span>
                </div>
                
                <p class="text-lg font-semibold text-gray-900 mb-2">Question</p>
                <p class="text-xl text-gray-800 leading-relaxed">{{ $quiz->question }}</p>
            </div>

            <!-- Quiz Options -->
            <div id="quiz-options" class="space-y-3">
                <button onclick="submitQuizAnswer('a')" class="quiz-option w-full text-left p-5 rounded-2xl border-2 border-gray-200 hover:border-purple-400 hover:bg-purple-50 transition-all duration-200 flex items-center gap-4 group" data-option="a">
                    <span class="w-10 h-10 rounded-2xl bg-gray-100 text-gray-600 font-bold text-sm flex items-center justify-center group-hover:bg-purple-100 group-hover:text-purple-600 transition">A</span>
                    <span class="text-base text-gray-700 font-medium">{{ $quiz->option_a }}</span>
                </button>
                <button onclick="submitQuizAnswer('b')" class="quiz-option w-full text-left p-5 rounded-2xl border-2 border-gray-200 hover:border-purple-400 hover:bg-purple-50 transition-all duration-200 flex items-center gap-4 group" data-option="b">
                    <span class="w-10 h-10 rounded-2xl bg-gray-100 text-gray-600 font-bold text-sm flex items-center justify-center group-hover:bg-purple-100 group-hover:text-purple-600 transition">B</span>
                    <span class="text-base text-gray-700 font-medium">{{ $quiz->option_b }}</span>
                </button>
                <button onclick="submitQuizAnswer('c')" class="quiz-option w-full text-left p-5 rounded-2xl border-2 border-gray-200 hover:border-purple-400 hover:bg-purple-50 transition-all duration-200 flex items-center gap-4 group" data-option="c">
                    <span class="w-10 h-10 rounded-2xl bg-gray-100 text-gray-600 font-bold text-sm flex items-center justify-center group-hover:bg-purple-100 group-hover:text-purple-600 transition">C</span>
                    <span class="text-base text-gray-700 font-medium">{{ $quiz->option_c }}</span>
                </button>
                <button onclick="submitQuizAnswer('d')" class="quiz-option w-full text-left p-5 rounded-2xl border-2 border-gray-200 hover:border-purple-400 hover:bg-purple-50 transition-all duration-200 flex items-center gap-4 group" data-option="d">
                    <span class="w-10 h-10 rounded-2xl bg-gray-100 text-gray-600 font-bold text-sm flex items-center justify-center group-hover:bg-purple-100 group-hover:text-purple-600 transition">D</span>
                    <span class="text-base text-gray-700 font-medium">{{ $quiz->option_d }}</span>
                </button>
            </div>

            <!-- Feedback Area -->
            <div id="quiz-feedback" class="mt-6 hidden"></div>

            <!-- Loading Indicator -->
            <div id="quiz-loading" class="mt-6 hidden">
                <div class="flex items-center justify-center gap-2 py-4">
                    <div class="w-2 h-2 rounded-full bg-purple-400 animate-bounce" style="animation-delay: 0s"></div>
                    <div class="w-2 h-2 rounded-full bg-purple-500 animate-bounce" style="animation-delay: 0.15s"></div>
                    <div class="w-2 h-2 rounded-full bg-purple-600 animate-bounce" style="animation-delay: 0.3s"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let quizSubmitting = false;

async function submitQuizAnswer(answer) {
    if (quizSubmitting) return;
    quizSubmitting = true;

    // Disable all options
    document.querySelectorAll('.quiz-option').forEach(el => {
        el.classList.add('pointer-events-none');
        el.classList.remove('hover:border-purple-400', 'hover:bg-purple-50');
    });

    // Highlight selected
    document.querySelector(`.quiz-option[data-option="${answer}"]`).classList.add('border-purple-500', 'bg-purple-50');

    // Show loading
    document.getElementById('quiz-loading').classList.remove('hidden');

    try {
        const response = await fetch('{{ route("student.courses.quiz.attempt", [$course->id, $lesson->id]) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ selected_answer: answer })
        });

        const data = await response.json();
        document.getElementById('quiz-loading').classList.add('hidden');
        
        const feedback = document.getElementById('quiz-feedback');
        feedback.classList.remove('hidden');

        if (data.is_correct) {
            feedback.innerHTML = `
                <div class="p-6 bg-green-50 border border-green-200 rounded-2xl text-center">
                    <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check-circle text-4xl text-green-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-green-800 mb-2">Correct!</h3>
                    <p class="text-sm text-green-600 mb-6">Great job! You answered correctly.</p>
                    <div class="flex items-center justify-center gap-3">
                        <a href="{{ route('student.courses.show', [$course->id, $lesson->id]) }}" class="px-6 py-2.5 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-2xl transition text-sm">
                            <i class="fas fa-arrow-left"></i> Back to Course
                        </a>
                    </div>
                </div>
            `;
        } else {
            const correctLetter = data.correct_answer.toUpperCase();
            const correctText = document.querySelector(`.quiz-option[data-option="${data.correct_answer}"]`).querySelector('span:last-child').textContent;
            
            // Highlight correct answer
            document.querySelector(`.quiz-option[data-option="${data.correct_answer}"]`).classList.add('border-green-500', 'bg-green-50');
            document.querySelector(`.quiz-option[data-option="${answer}"]`).classList.add('border-red-500', 'bg-red-50');

            feedback.innerHTML = `
                <div class="p-6 bg-red-50 border border-red-200 rounded-2xl text-center">
                    <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-times-circle text-4xl text-red-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-red-800 mb-2">Better luck next time!</h3>
                    <p class="text-sm text-red-600 mb-2">The correct answer was:</p>
                    <p class="text-lg font-bold text-red-700 mb-6">${correctLetter}. ${correctText}</p>
                    <div class="flex items-center justify-center">
                        <a href="{{ route('student.courses.show', [$course->id, $lesson->id]) }}" class="px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-2xl transition text-sm">
                            <i class="fas fa-arrow-left"></i> Back to Course
                        </a>
                    </div>
                </div>
            `;
            quizSubmitting = false;
        }
    } catch (error) {
        document.getElementById('quiz-loading').classList.add('hidden');
        document.getElementById('quiz-feedback').classList.remove('hidden');
        document.getElementById('quiz-feedback').innerHTML = `
            <div class="p-6 bg-red-50 border border-red-200 rounded-2xl text-center">
                <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-exclamation-triangle text-4xl text-red-600"></i>
                </div>
                <h3 class="text-xl font-bold text-red-800 mb-2">Something went wrong</h3>
                <p class="text-sm text-red-600 mb-6">Please try again later.</p>
                <button onclick="location.reload()" class="px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-2xl transition text-sm">
                    <i class="fas fa-redo"></i> Try Again
                </button>
            </div>
        `;
        quizSubmitting = false;
    }
}
</script>
@endsection