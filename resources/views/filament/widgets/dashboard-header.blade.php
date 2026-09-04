<div class="fi-wi bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="relative">
        <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 via-purple-500 to-indigo-600 opacity-10 dark:opacity-20"></div>
        
        <div class="relative px-6 py-5 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <img src="{{ asset('apple-touch-icon.png') }}" 
                         alt="TaskForge" 
                         class="w-10 h-10 rounded-xl shadow-lg" />
                    
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                            <span class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                                TaskForge
                            </span>
                            <span class="text-gray-600 dark:text-gray-300"> Admin</span>
                        </h1>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                            {{ now()->format('l, F j, Y • g:i A') }}
                        </p>
                    </div>
                </div>
                
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-green-50 dark:bg-green-900/30 rounded-full">
                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                        <span class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 font-medium">System Online</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
