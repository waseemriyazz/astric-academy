@extends('layouts.admin')

@section('header', 'Edit Course')

@section('content')
<div class="max-w-4xl bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
    <div class="flex items-center justify-between mb-8">
        <h3 class="text-xl font-bold text-gray-900">Edit Course Details</h3>
        <a href="{{ route('admin.courses.index') }}" class="text-sm font-medium text-gray-500 hover:text-indigo-600 transition">
            <i class="fas fa-arrow-left mr-1"></i> Back to List
        </a>
    </div>
    
    <form action="{{ route('admin.courses.update', $course->id) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Course Title</label>
            <input type="text" name="title" required value="{{ old('title', $course->title) }}" class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
            @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="4" class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">{{ old('description', $course->description) }}</textarea>
            @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <select name="category" class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Select Category</option>
                    <option value="Marketing" {{ old('category', $course->category) == 'Marketing' ? 'selected' : '' }}>Marketing</option>
                    <option value="Development" {{ old('category', $course->category) == 'Development' ? 'selected' : '' }}>Development</option>
                    <option value="Design" {{ old('category', $course->category) == 'Design' ? 'selected' : '' }}>Design</option>
                    <option value="Strategy" {{ old('category', $course->category) == 'Strategy' ? 'selected' : '' }}>Strategy</option>
                    <option value="Data" {{ old('category', $course->category) == 'Data' ? 'selected' : '' }}>Data</option>
                    <option value="AI" {{ old('category', $course->category) == 'AI' ? 'selected' : '' }}>AI</option>
                </select>
                @error('category') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Duration</label>
                <input type="text" name="duration" value="{{ old('duration', $course->duration) }}" class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. 8 Weeks">
                @error('duration') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Icon Name</label>
                <select name="icon_name" class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Select Icon</option>
                    <option value="Rocket" {{ old('icon_name', $course->icon_name) == 'Rocket' ? 'selected' : '' }}>Rocket</option>
                    <option value="Search" {{ old('icon_name', $course->icon_name) == 'Search' ? 'selected' : '' }}>Search</option>
                    <option value="Users" {{ old('icon_name', $course->icon_name) == 'Users' ? 'selected' : '' }}>Users</option>
                    <option value="Mail" {{ old('icon_name', $course->icon_name) == 'Mail' ? 'selected' : '' }}>Mail</option>
                    <option value="Code" {{ old('icon_name', $course->icon_name) == 'Code' ? 'selected' : '' }}>Code</option>
                    <option value="Image" {{ old('icon_name', $course->icon_name) == 'Image' ? 'selected' : '' }}>Image</option>
                    <option value="Palette" {{ old('icon_name', $course->icon_name) == 'Palette' ? 'selected' : '' }}>Palette</option>
                    <option value="LayoutDashboard" {{ old('icon_name', $course->icon_name) == 'LayoutDashboard' ? 'selected' : '' }}>LayoutDashboard</option>
                    <option value="BarChart3" {{ old('icon_name', $course->icon_name) == 'BarChart3' ? 'selected' : '' }}>BarChart3</option>
                    <option value="MonitorPlay" {{ old('icon_name', $course->icon_name) == 'MonitorPlay' ? 'selected' : '' }}>MonitorPlay</option>
                    <option value="Zap" {{ old('icon_name', $course->icon_name) == 'Zap' ? 'selected' : '' }}>Zap</option>
                    <option value="Bot" {{ old('icon_name', $course->icon_name) == 'Bot' ? 'selected' : '' }}>Bot</option>
                    <option value="Brain" {{ old('icon_name', $course->icon_name) == 'Brain' ? 'selected' : '' }}>Brain</option>
                    <option value="MessageSquare" {{ old('icon_name', $course->icon_name) == 'MessageSquare' ? 'selected' : '' }}>MessageSquare</option>
                    <option value="Workflow" {{ old('icon_name', $course->icon_name) == 'Workflow' ? 'selected' : '' }}>Workflow</option>
                    <option value="Sparkles" {{ old('icon_name', $course->icon_name) == 'Sparkles' ? 'selected' : '' }}>Sparkles</option>
                    <option value="PieChart" {{ old('icon_name', $course->icon_name) == 'PieChart' ? 'selected' : '' }}>PieChart</option>
                    <option value="Briefcase" {{ old('icon_name', $course->icon_name) == 'Briefcase' ? 'selected' : '' }}>Briefcase</option>
                    <option value="TrendingUp" {{ old('icon_name', $course->icon_name) == 'TrendingUp' ? 'selected' : '' }}>TrendingUp</option>
                    <option value="Laptop" {{ old('icon_name', $course->icon_name) == 'Laptop' ? 'selected' : '' }}>Laptop</option>
                    <option value="ShieldCheck" {{ old('icon_name', $course->icon_name) == 'ShieldCheck' ? 'selected' : '' }}>ShieldCheck</option>
                </select>
                @error('icon_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Number of Tools</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-tools text-gray-400"></i>
                    </div>
                    <input type="number" name="tools_count" min="0" value="{{ old('tools_count', $course->tools_count ?? 0) }}" class="w-full pl-9 rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                @error('tools_count') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Price Min (USD)</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500">$</span>
                    </div>
                    <input type="number" name="price_min" step="0.01" min="0" value="{{ old('price_min', $course->price_min) }}" class="w-full pl-7 rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                @error('price_min') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Price Max (USD)</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500">$</span>
                    </div>
                    <input type="number" name="price_max" step="0.01" min="0" value="{{ old('price_max', $course->price_max) }}" class="w-full pl-7 rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                @error('price_max') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Features (one per line)</label>
            <textarea name="features" rows="4" class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g.&#10;Practical Strategies&#10;Real-world Tools">{{ old('features', is_array($course->features) ? implode("\n", $course->features) : $course->features) }}</textarea>
            <p class="mt-1 text-xs text-gray-500">Enter each feature on a new line.</p>
            @error('features') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end gap-3 pt-6">
            <a href="{{ route('admin.courses.index') }}" class="px-5 py-2.5 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Cancel</a>
            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 rounded-lg text-sm font-medium text-white transition shadow-sm">
                Save Changes
            </button>
        </div>
    </form>

    <!-- Certificate Configuration Section -->
    <div class="mt-8 pt-8 border-t border-gray-200">
        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
            <i class="fas fa-certificate text-indigo-600"></i> Certificate Configuration
        </h3>
        
        <form action="{{ route('admin.courses.certificate-config', $course->id) }}" method="POST" class="space-y-5">
            @csrf
            
            <p class="text-sm text-gray-500 mb-4">
                Customize how certificates will look for students who complete this course.
            </p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Signature Name</label>
                    <input type="text" name="signature_name" 
                           value="{{ old('signature_name', $course->certificate_config['signature_name'] ?? 'Astryx Academy') }}" 
                           class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="e.g. John Doe">
                    @error('signature_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Signature Title</label>
                    <input type="text" name="signature_title" 
                           value="{{ old('signature_title', $course->certificate_config['signature_title'] ?? 'Authorized Signature') }}" 
                           class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="e.g. Director of Education">
                    @error('signature_title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 rounded-lg text-sm font-medium text-white transition shadow-sm">
                    <i class="fas fa-save"></i> Update Certificate Config
                </button>
            </div>
        </form>
    </div>
</div>
@endsection