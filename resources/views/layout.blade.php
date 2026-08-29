<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JustSubs Dashboard</title>
    <link href="{{ asset('vendor/justsubs/css/app.css') }}" rel="stylesheet">
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <div class="w-64 bg-white border-r border-gray-200 flex flex-col hidden md:flex">
        <div class="h-16 flex items-center px-6 border-b border-gray-200">
            <span class="text-xl font-bold text-indigo-600">JustSubs</span>
        </div>
        <div class="flex-1 overflow-y-auto py-4">
            <nav class="space-y-1 px-3">
                <a href="{{ route('justsubs.dashboard') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('justsubs.dashboard') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    Dashboard
                </a>
                <a href="{{ route('justsubs.plans.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('justsubs.plans.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    Plans
                </a>
                <a href="{{ route('justsubs.subscriptions.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('justsubs.subscriptions.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    Subscriptions
                </a>
                <a href="{{ route('justsubs.subscribers.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('justsubs.subscribers.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    Subscribers
                </a>
                <a href="{{ route('justsubs.invoices.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('justsubs.invoices.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    Invoices
                </a>
                <a href="{{ route('justsubs.payments.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('justsubs.payments.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    Payments
                </a>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <!-- Top Nav -->
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6">
            <div class="md:hidden">
                <span class="text-xl font-bold text-indigo-600">JustSubs</span>
            </div>
            <div class="flex items-center space-x-4 ml-auto">
                <span class="text-sm text-gray-500">v1.0.0</span>
            </div>
        </header>

        <!-- Flash Messages -->
        @if(session()->has('success'))
        <div class="bg-green-50 border-l-4 border-green-400 p-4 mx-6 mt-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <!-- Icon -->
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">
                        {{ session('success') }}
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- Page Content -->
        <main class="flex-1 overflow-y-auto p-6">
            @yield('content')
        </main>
    </div>
</body>
</html>
