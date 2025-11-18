<div class="space-y-6">

    <!-- Abas de Navegação por Nível -->
    <div class="glass-effect p-2 rounded-xl flex items-center space-x-2">
        @for ($i = 1; $i <= 4; $i++)
            <button
                wire:click="setActiveLevel({{ $i }})"
                class="flex-1 px-4 py-3 rounded-lg font-semibold text-sm sm:text-base transition-all duration-300 ease-in-out focus:outline-none
                    {{ $activeLevel == $i ? 'bg-primary text-white shadow-lg' : 'text-gray-400 hover:text-white hover:bg-white/5' }}"
            >
                Level {{ $i }}
                <span class="ml-1 px-2 py-0.5 rounded-full text-xs {{ $i == $activeLevel ? 'bg-white/20' : 'bg-gray-700/50' }}">
                    {{ isset($referralsByLevel[$i]) ? count($referralsByLevel[$i]) : 0 }}
                </span>
            </button>
        @endfor
    </div>

    <!-- Tabela de Indicados -->
    <div class="glass-effect rounded-xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table min-w-full">
                <thead class="bg-white/5">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                            UserName
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                            Total Invested
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                            Bonus Generated (for You)
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                            Active Bots
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                            Date of 1st Investment.
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
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full gradient-primary flex items-center justify-center text-white font-bold">
                                            {{ substr($user['name'], 0, 1) }}
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-white">{{ $user['name'] }}</div>
                                            <div class="text-sm text-gray-400">{{ $user['username'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($user['has_active_investment'])
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif 
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                   ${{ number_format($user['total_invested'], 2) }} 
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-yellow-400">
                                    ${{ number_format($bonusGerado, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300 text-center">
                                    {{ $user['active_bots_count'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                    {{ $user['first_investment_at'] ? \Carbon\Carbon::parse($user['first_investment_at'])->format('d/m/Y H:i') : 'N/A' }}
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                No suitable candidates were found for this level.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>