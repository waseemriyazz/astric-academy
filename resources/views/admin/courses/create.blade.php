@extends('layouts.admin')

@section('header', 'Create New Course')

@section('content')
<div class="max-w-4xl bg-white rounded-2xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-gray-100 p-8">
    <div class="flex items-center justify-between mb-8 pb-6 border-b border-gray-100">
        <div>
            <h3 class="text-xl font-bold text-gray-900 mb-1">Course Fundamentals</h3>
            <p class="text-sm text-gray-500">Don't worry, you can always edit this later. Next, you'll be able to add lessons.</p>
        </div>
        <a href="{{ route('admin.courses.index') }}" class="text-sm font-medium text-gray-500 hover:text-blue-600 transition flex items-center gap-1">
            <i class="fas fa-arrow-left"></i> Back to Courses
        </a>
    </div>
    
    <form action="{{ route('admin.courses.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Course Title *</label>
            <input type="text" name="title" required value="{{ old('title') }}" class="w-full rounded-2xl border-gray-200 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm" placeholder="e.g. Digital Marketing">
            @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Course Description</label>
            <textarea name="description" rows="4" class="w-full rounded-2xl border-gray-200 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm" placeholder="Provide a compelling overview of what students will achieve in this course...">{{ old('description') }}</textarea>
            @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Category</label>
                <select name="category" class="w-full rounded-2xl border-gray-200 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
                    <option value="">Select Category</option>
                    <option value="Marketing" {{ old('category') == 'Marketing' ? 'selected' : '' }}>Marketing</option>
                    <option value="Development" {{ old('category') == 'Development' ? 'selected' : '' }}>Development</option>
                    <option value="Design" {{ old('category') == 'Design' ? 'selected' : '' }}>Design</option>
                    <option value="Strategy" {{ old('category') == 'Strategy' ? 'selected' : '' }}>Strategy</option>
                    <option value="Data" {{ old('category') == 'Data' ? 'selected' : '' }}>Data</option>
                    <option value="AI" {{ old('category') == 'AI' ? 'selected' : '' }}>AI</option>
                </select>
                @error('category') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Duration</label>
                <input type="text" name="duration" value="{{ old('duration') }}" class="w-full rounded-2xl border-gray-200 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm" placeholder="e.g. 8 Weeks">
                @error('duration') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Icon Name</label>
                <select name="icon_name" class="w-full rounded-2xl border-gray-200 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
                    <option value="">Select Icon</option>
                    <option value="Rocket" {{ old('icon_name') == 'Rocket' ? 'selected' : '' }}>Rocket</option>
                    <option value="Search" {{ old('icon_name') == 'Search' ? 'selected' : '' }}>Search</option>
                    <option value="Users" {{ old('icon_name') == 'Users' ? 'selected' : '' }}>Users</option>
                    <option value="Mail" {{ old('icon_name') == 'Mail' ? 'selected' : '' }}>Mail</option>
                    <option value="Code" {{ old('icon_name') == 'Code' ? 'selected' : '' }}>Code</option>
                    <option value="Image" {{ old('icon_name') == 'Image' ? 'selected' : '' }}>Image</option>
                    <option value="Palette" {{ old('icon_name') == 'Palette' ? 'selected' : '' }}>Palette</option>
                    <option value="LayoutDashboard" {{ old('icon_name') == 'LayoutDashboard' ? 'selected' : '' }}>LayoutDashboard</option>
                    <option value="BarChart3" {{ old('icon_name') == 'BarChart3' ? 'selected' : '' }}>BarChart3</option>
                    <option value="MonitorPlay" {{ old('icon_name') == 'MonitorPlay' ? 'selected' : '' }}>MonitorPlay</option>
                    <option value="Zap" {{ old('icon_name') == 'Zap' ? 'selected' : '' }}>Zap</option>
                    <option value="Bot" {{ old('icon_name') == 'Bot' ? 'selected' : '' }}>Bot</option>
                    <option value="Brain" {{ old('icon_name') == 'Brain' ? 'selected' : '' }}>Brain</option>
                    <option value="MessageSquare" {{ old('icon_name') == 'MessageSquare' ? 'selected' : '' }}>MessageSquare</option>
                    <option value="Workflow" {{ old('icon_name') == 'Workflow' ? 'selected' : '' }}>Workflow</option>
                    <option value="Sparkles" {{ old('icon_name') == 'Sparkles' ? 'selected' : '' }}>Sparkles</option>
                    <option value="PieChart" {{ old('icon_name') == 'PieChart' ? 'selected' : '' }}>PieChart</option>
                    <option value="Briefcase" {{ old('icon_name') == 'Briefcase' ? 'selected' : '' }}>Briefcase</option>
                    <option value="TrendingUp" {{ old('icon_name') == 'TrendingUp' ? 'selected' : '' }}>TrendingUp</option>
                    <option value="Laptop" {{ old('icon_name') == 'Laptop' ? 'selected' : '' }}>Laptop</option>
                    <option value="ShieldCheck" {{ old('icon_name') == 'ShieldCheck' ? 'selected' : '' }}>ShieldCheck</option>
                </select>
                @error('icon_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Number of Tools</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-tools text-gray-400"></i>
                    </div>
                    <input type="number" name="tools_count" min="0" value="{{ old('tools_count', '0') }}" class="w-full pl-9 rounded-2xl border-gray-200 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
                </div>
                <p class="mt-1 text-xs text-gray-500">How many tools are taught in this program?</p>
                @error('tools_count') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Price Min (USD)</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500">$</span>
                    </div>
                    <input type="number" name="price_min" step="0.01" min="0" value="{{ old('price_min', '0') }}" class="w-full pl-7 rounded-2xl border-gray-200 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
                </div>
                @error('price_min') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Price Max (USD)</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500">$</span>
                    </div>
                    <input type="number" name="price_max" step="0.01" min="0" value="{{ old('price_max', '0') }}" class="w-full pl-7 rounded-2xl border-gray-200 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
                </div>
                @error('price_max') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Features (one per line)</label>
            <textarea name="features" rows="4" class="w-full rounded-2xl border-gray-200 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm" placeholder="e.g.&#10;Practical Strategies&#10;Real-world Tools&#10;Job-Ready Focus">{{ old('features') }}</textarea>
            <p class="mt-1 text-xs text-gray-500">Enter each feature on a new line.</p>
            @error('features') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="pt-6 border-t border-gray-100 flex items-center justify-end gap-3 mt-8">
            <a href="{{ route('admin.courses.index') }}" class="px-5 py-2 text-gray-600 font-medium hover:bg-gray-50 border border-gray-200 rounded-2xl transition text-sm">Cancel</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-5 rounded-2xl transition shadow-sm flex items-center gap-2 text-sm">
                <span>Create & Continue</span>
                <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </form>
</div>
@endsection