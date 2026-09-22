@extends('justsubs::layout')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Edit Plan: {{ $plan->name }}</h1>
</div>

<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden max-w-3xl transition-colors duration-200">
    <form action="{{ route('justsubs.plans.update', $plan) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="px-6 py-5 space-y-6">
            @include('justsubs::plans.form')
        </div>
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 flex justify-end">
            <a href="{{ route('justsubs.plans.index') }}" class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700 mr-3">Cancel</a>
            <button type="submit" class="bg-indigo-600 border border-transparent text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700 dark:hover:bg-indigo-500">Update Plan</button>
        </div>
    </form>
</div>
@endsection
