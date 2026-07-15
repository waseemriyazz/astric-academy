<x-guest-layout>
    <!-- Header -->
    <header class="w-full flex justify-between items-center px-4 sm:px-8 py-4 sm:py-6">
        <div class="flex-shrink-0">
            <img src="{{ asset('images/logo-1.jpeg') }}" alt="Astryx Academy" class="h-10 sm:h-16 object-contain mix-blend-multiply">
        </div>
        <div class="flex items-center text-gray-600 text-xs sm:text-sm text-right sm:text-left">
            <i class="fas fa-headset text-base sm:text-xl mr-2 sm:mr-3 text-indigo-600"></i>
            <div class="text-left">
                <p class="font-semibold text-gray-900 leading-none sm:leading-normal mb-0.5 sm:mb-0">Need Help?</p>
                <a href="mailto:support@astryxacademy.com" class="text-gray-500 hover:text-indigo-600 transition leading-none sm:leading-normal">support@astryxacademy.com</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="w-full max-w-[90rem] mx-auto px-4 sm:px-8 lg:px-12 py-6 sm:py-10 flex flex-col-reverse lg:flex-row items-stretch lg:items-center justify-between gap-8 sm:gap-12 lg:gap-16">
        
        <!-- Left Side: Branding & Info -->
        <div class="w-full lg:w-3/5 relative bg-white/40 rounded-3xl p-6 sm:p-8 lg:p-14 overflow-hidden shadow-sm border border-white/50 backdrop-blur-md">
            <!-- Decorative background elements -->
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-64 h-64 rounded-full bg-purple-100 opacity-50 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 rounded-full bg-blue-100 opacity-50 blur-3xl"></div>

            <div class="relative z-10">
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 leading-tight mb-4">
                    Welcome to <br>
                    <span class="text-indigo-600">Astryx Academy</span>
                </h1>
                <p class="text-gray-600 text-base sm:text-lg mb-8 sm:mb-10 max-w-md">
                    Your journey to mastering skills starts here. Access your courses, track your progress and achieve your goals with us.
                </p>

                <div class="space-y-6 mb-12">
                    <!-- Feature 1 -->
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 mt-1">
                            <i class="fas fa-book-open text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900">Expert Designed Courses</h3>
                            <p class="text-gray-500 text-sm">Industry-relevant and practical content.</p>
                        </div>
                    </div>
                    <!-- Feature 2 -->
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mt-1">
                            <i class="fas fa-chart-line text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900">Track Your Progress</h3>
                            <p class="text-gray-500 text-sm">Monitor your learning and achievements.</p>
                        </div>
                    </div>
                    <!-- Feature 3 -->
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 mt-1">
                            <i class="fas fa-star text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900">Learn Anytime, Anywhere</h3>
                            <p class="text-gray-500 text-sm">Unlimited access on any device.</p>
                        </div>
                    </div>
                </div>

                <!-- Call to action card -->
                <div class="bg-[#2e407b] rounded-2xl p-6 flex flex-col sm:flex-row items-center justify-between shadow-xl relative overflow-hidden">
                    <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="flex items-center gap-4 relative z-10">
                        <div class="w-14 h-14 rounded-full border border-white/20 flex items-center justify-center text-white">
                            <i class="fas fa-trophy text-2xl"></i>
                        </div>
                        <div>
                            <h4 class="text-white font-semibold text-lg">Start Your Success Story</h4>
                            <p class="text-blue-100 text-xs sm:text-sm mt-1">Join thousands of students who are building their future with Astryx Academy.</p>
                        </div>
                    </div>
                    <a href="https://astryxacademy.com/#courses" class="mt-4 sm:mt-0 px-5 py-2.5 bg-white text-[#2e407b] font-medium rounded-lg text-sm whitespace-nowrap hover:bg-gray-50 transition relative z-10 shadow-sm flex items-center">
                        Explore Courses <i class="fas fa-arrow-right ml-2 text-xs"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="w-full lg:w-2/5 max-w-xl mx-auto relative z-10">
            <div class="bg-white rounded-[2rem] p-6 sm:p-8 lg:p-12 shadow-[0_8px_30px_rgb(0,0,0,0.08)] border border-gray-100">
                <div class="text-center mb-6 sm:mb-8">
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">Student Portal Login</h2>
                    <p class="text-gray-500 text-xs sm:text-sm">Welcome back! Please log in with your email and password.</p>
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                                <i class="far fa-envelope"></i>
                            </div>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                                class="w-full pl-11 pr-4 py-3.5 bg-white border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter your email">
                        </div>
                        @if($errors->has('email'))
                            <div class="mt-3 bg-red-50 rounded-xl p-4 flex items-start gap-3 border border-red-100">
                                <i class="fas fa-exclamation-circle text-red-500 mt-0.5 text-lg"></i>
                                <p class="text-sm font-medium text-red-800 leading-relaxed">
                                    {!! $errors->first('email') !!}
                                </p>
                            </div>
                        @endif
                    </div>

                    <div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-lock"></i>
                            </div>
                            <input id="password" type="password" name="password" autocomplete="current-password"
                                class="w-full pl-11 pr-11 py-3.5 bg-white border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter your password">
                            <button type="button" onclick="const p = document.getElementById('password'); const i = this.querySelector('i'); if(p.type === 'password'){ p.type = 'text'; i.classList.remove('fa-eye-slash'); i.classList.add('fa-eye'); } else { p.type = 'password'; i.classList.remove('fa-eye'); i.classList.add('fa-eye-slash'); }" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                <i class="far fa-eye-slash"></i>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between pt-1">
                        <label for="remember_me" class="flex items-center cursor-pointer">
                            <input id="remember_me" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer" name="remember">
                            <span class="ml-2 text-sm text-gray-600">Remember me</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                                Forgot password?
                            </a>
                        @endif
                    </div>

                    <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-600 hover:to-purple-600 text-white font-medium rounded-xl shadow-[0_4px_14px_0_rgb(99,102,241,0.39)] transition transform hover:-translate-y-0.5">
                        Login to Dashboard
                    </button>
                </form>

            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full py-6 px-8 border-t border-gray-200 mt-auto flex justify-center items-center text-sm text-gray-500">
        <p>&copy; 2026 Astryx Academy. All rights reserved.</p>
    </footer>
</x-guest-layout>
