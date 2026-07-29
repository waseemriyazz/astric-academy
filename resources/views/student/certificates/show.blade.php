@extends('layouts.student')

@section('header')
<div class="flex items-center gap-3">
    <a href="{{ route('student.certificates.index') }}" class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-brand-600 hover:border-brand-200 transition shadow-sm">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="text-xl font-bold text-gray-900 leading-tight">Certificate of Completion</h1>
        <p class="text-xs text-gray-500">{{ $course->title }}</p>
    </div>
</div>
@endsection

@section('header_actions')
<a href="{{ route('student.certificates.download', $course->id) }}" 
   class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-2xl transition shadow-sm">
    <i class="fas fa-download"></i> Download PDF
</a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Certificate Display -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
        <!-- Certificate Border Decoration -->
        <div class="m-3 border-4 border-double border-brand-100 rounded-2xl overflow-hidden">
            <!-- Certificate Header -->
            <div class="bg-gradient-to-r from-brand-600 via-purple-600 to-brand-600 p-8 text-white text-center relative">
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute top-0 left-0 w-32 h-32 border-t-4 border-l-4 border-white rounded-tr-full"></div>
                    <div class="absolute bottom-0 right-0 w-32 h-32 border-b-4 border-r-4 border-white rounded-bl-full"></div>
                </div>
                <div class="relative z-10">
                    <div class="w-20 h-20 mx-auto mb-4 bg-white/20 rounded-full flex items-center justify-center">
                        <i class="fas fa-certificate text-4xl text-white"></i>
                    </div>
                    <h1 class="text-3xl font-bold tracking-wide mb-2">CERTIFICATE OF COMPLETION</h1>
                    <p class="text-brand-200 text-sm font-medium">This certificate is proudly presented to</p>
                </div>
            </div>

            <!-- Certificate Body -->
            <div class="p-10 text-center">
                <!-- Student Name -->
                <h2 class="text-4xl font-bold text-gray-900 mb-4 font-serif">{{ $certificate->user->name }}</h2>
                
                <div class="w-24 h-0.5 bg-gradient-to-r from-brand-400 to-cyan-300 mx-auto mb-6"></div>

                <!-- Description -->
                <p class="text-lg text-gray-600 mb-2">For successfully completing the course</p>
                <h3 class="text-2xl font-bold text-brand-700 mb-8">{{ $course->title }}</h3>

                <!-- Details Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-2xl mx-auto mb-10">
                    <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wider mb-1">Completion Date</p>
                        <p class="text-sm font-bold text-gray-900">{{ $certificate->issued_at ? $certificate->issued_at->format('F d, Y') : 'N/A' }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wider mb-1">Serial Number</p>
                        <p class="text-sm font-bold text-gray-900 font-mono">{{ $certificate->serial_number }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wider mb-1">Certificate ID</p>
                        <p class="text-sm font-bold text-gray-900 font-mono">#{{ str_pad($certificate->id, 6, '0', STR_PAD_LEFT) }}</p>
                    </div>
                </div>

                <!-- Signature & Seal -->
                <div class="flex items-end justify-center gap-16 mt-8 pt-8 border-t border-gray-100">
                    <div class="text-center">
                        <div class="w-32 h-16 mx-auto mb-2 border-b-2 border-gray-300">
                            <img src="{{ asset('images/logo-new.jpeg') }}" alt="Signature" class="h-full mx-auto opacity-60 object-contain">
                        </div>
                        <p class="text-sm font-bold text-gray-900">Skill Stryx</p>
                        <p class="text-xs text-gray-500">Authorized Signature</p>
                    </div>
                    <div class="text-center">
                        <div class="w-20 h-20 mx-auto mb-2 rounded-full border-2 border-brand-200 flex items-center justify-center bg-brand-50">
                            <i class="fas fa-certificate text-3xl text-brand-400"></i>
                        </div>
                        <p class="text-sm font-bold text-gray-900">Official Seal</p>
                        <p class="text-xs text-gray-500">Skill Stryx</p>
                    </div>
                </div>
            </div>

            <!-- Certificate Footer -->
            <div class="bg-gray-50 px-8 py-4 border-t border-gray-100">
                <p class="text-xs text-gray-400 text-center">
                    This certificate is digitally issued and can be verified using the unique serial number above.
                    Certificate Serial: <span class="font-mono font-semibold text-gray-500">{{ $certificate->serial_number }}</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Verification Badge -->
    <div class="mt-6 bg-green-50 border border-green-200 rounded-2xl p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center shrink-0">
            <i class="fas fa-check-circle text-green-600 text-lg"></i>
        </div>
        <div>
            <p class="text-sm font-bold text-green-800">Verified Certificate</p>
            <p class="text-xs text-green-600">This certificate has been issued by Skill Stryx and verified as authentic.</p>
        </div>
    </div>
</div>
@endsection