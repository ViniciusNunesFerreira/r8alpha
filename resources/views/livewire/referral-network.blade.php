<div class="space-y-4 sm:space-y-6 w-full max-w-full">

    <!-- Abas de Navegação por Nível -->
    <div class="glass-effect p-2 rounded-xl w-full overflow-x-auto">
        <div class="flex items-center space-x-2 min-w-max">
            @for ($i = 1; $i <= 4; $i++)
                <button wire:click="setActiveLevel({{ $i }})"
                    class="flex-1 min-w-[80px] sm:min-w-[100px] py-2 sm:py-3 rounded-lg font-semibold text-xs sm:text-sm md:text-base transition-all duration-300 ease-in-out focus:outline-none whitespace-nowrap
                        {{ $activeLevel == $i ? 'bg-primary text-white shadow-lg' : 'text-gray-400 hover:text-white hover:bg-white/5' }}"
                >
                    Level {{ $i }}
                    <span class="ml-1 px-1.5 sm:px-2 py-0.5 rounded-full text-xs {{ $i == $activeLevel ? 'bg-white/20' : 'bg-gray-700/50' }}">
                        {{ isset($referralsByLevel[$i]) ? count($referralsByLevel[$i]) : 0 }}
                    </span>
                </button>
            @endfor
        </div>
    </div>

    <!-- Container da Tabela com Scroll Horizontal -->
    <div class="glass-effect rounded-xl shadow-xl overflow-hidden w-full">
        <div class="w-full overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[800px] divide-y divide-gray-800/50">
                <thead class="bg-white/5">
                    <tr>
                        <th scope="col" class="px-3 sm:px-4 md:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider whitespace-nowrap">
                            UserName
                        </th>
                        <th scope="col" class="px-3 sm:px-4 md:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider whitespace-nowrap">
                            Status
                        </th>
                        <th scope="col" class="px-3 sm:px-4 md:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider whitespace-nowrap">
                            Total Invested
                        </th>
                        <th scope="col" class="px-3 sm:px-4 md:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider whitespace-nowrap">
                            Bonus Generated
                        </th>
                        <th scope="col" class="px-3 sm:px-4 md:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider whitespace-nowrap">
                            Active Bots
                        </th>
                        <th scope="col" class="px-3 sm:px-4 md:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider whitespace-nowrap">
                            Date of 1st Investment
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/50">
                    @if (isset($referralsByLevel[$activeLevel]) && count($referralsByLevel[$activeLevel]) > 0)
                        @foreach ($referralsByLevel[$activeLevel] as $referral)
                            @php
                                $user = $referral['user'];
                                $bonusGerado = $commissionsGenerated[$user['id']] ?? 0;
                            @endphp
                            <tr class="hover:bg-white/5 transition-colors duration-200">
                                <!-- Username Column -->
                                <td class="px-3 sm:px-4 md:px-6 py-3 sm:py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 sm:h-10 sm:w-10 rounded-full gradient-primary flex items-center justify-center text-white font-bold text-xs sm:text-sm">
                                            {{ substr($user['name'], 0, 1) }}
                                        </div>
                                        <div class="ml-3 sm:ml-4 min-w-0">
                                            <div class="text-xs sm:text-sm font-medium text-white truncate max-w-[120px] sm:max-w-none">
                                                {{ $user['name'] }}
                                            </div>
                                            <div class="text-xs text-gray-400 truncate max-w-[120px] sm:max-w-none">
                                                {{ $user['username'] }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Status Column -->
                                <td class="px-3 sm:px-4 md:px-6 py-3 sm:py-4 whitespace-nowrap">
                                    @if ($user['has_active_investment'])
                                        <span class="badge badge-success text-xs">Active</span>
                                    @else
                                        <span class="badge badge-danger text-xs">Inactive</span>
                                    @endif 
                                </td>
                                
                                <!-- Total Invested Column -->
                                <td class="px-3 sm:px-4 md:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-300 font-medium">
                                   ${{ number_format($user['total_invested'], 2) }} 
                                </td>
                                
                                <!-- Bonus Generated Column -->
                                <td class="px-3 sm:px-4 md:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-semibold text-yellow-400">
                                    ${{ number_format($bonusGerado, 2) }}
                                </td>
                                
                                <!-- Active Bots Column -->
                                <td class="px-3 sm:px-4 md:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-300 text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary/20 text-primary font-semibold">
                                        {{ $user['active_bots_count'] }}
                                    </span>
                                </td>
                                
                                <!-- Date Column -->
                                <td class="px-3 sm:px-4 md:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-400">
                                    @if ($user['first_investment_at'])
                                        <div class="flex flex-col">
                                            <span>{{ \Carbon\Carbon::parse($user['first_investment_at'])->format('d/m/Y') }}</span>
                                            <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($user['first_investment_at'])->format('H:i') }}</span>
                                        </div>
                                    @else
                                        <span class="text-gray-500">N/A</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="px-4 sm:px-6 py-8 sm:py-12 text-center">
                                <div class="flex flex-col items-center justify-center space-y-3">
                                    <svg class="w-12 h-12 sm:w-16 sm:h-16 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <div class="text-center">
                                        <p class="text-gray-400 font-medium text-sm sm:text-base">No referrals found</p>
                                        <p class="text-gray-500 text-xs sm:text-sm mt-1">There are no referrals at this level yet</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        
        <!-- Scroll Indicator for Mobile -->
        @if (isset($referralsByLevel[$activeLevel]) && count($referralsByLevel[$activeLevel]) > 0)
        <div class="block sm:hidden p-2 bg-gray-800/30 border-t border-gray-800/50 text-center">
            <p class="text-xs text-gray-400 flex items-center justify-center">
                <svg class="w-4 h-4 mr-1 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                </svg>
                Swipe to see more
            </p>
        </div>
        @endif
    </div>

    <!-- Summary Stats (Optional - Mobile Friendly) -->
    @if (isset($referralsByLevel[$activeLevel]) && count($referralsByLevel[$activeLevel]) > 0)
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <div class="glass-effect rounded-lg p-3 sm:p-4">
            <p class="text-xs text-gray-400 mb-1">Total Referrals</p>
            <p class="text-lg sm:text-2xl font-bold text-white">{{ count($referralsByLevel[$activeLevel]) }}</p>
        </div>
        
        <div class="glass-effect rounded-lg p-3 sm:p-4">
            <p class="text-xs text-gray-400 mb-1">Active</p>
            <p class="text-lg sm:text-2xl font-bold text-success">
                {{ collect($referralsByLevel[$activeLevel])->where('user.has_active_investment', true)->count() }}
            </p>
        </div>
        
        <div class="glass-effect rounded-lg p-3 sm:p-4">
            <p class="text-xs text-gray-400 mb-1">Total Invested</p>
            <p class="text-lg sm:text-2xl font-bold text-primary">
                ${{ number_format(collect($referralsByLevel[$activeLevel])->sum('user.total_invested'), 2) }}
            </p>
        </div>
        
        <div class="glass-effect rounded-lg p-3 sm:p-4">
            <p class="text-xs text-gray-400 mb-1">Your Bonus</p>
            <p class="text-lg sm:text-2xl font-bold text-yellow-400">
                ${{ number_format(collect($referralsByLevel[$activeLevel])->sum(function($ref) use ($commissionsGenerated) {
                    return $commissionsGenerated[$ref['user']['id']] ?? 0;
                }), 2) }}
            </p>
        </div>
    </div>
    @endif
</div>