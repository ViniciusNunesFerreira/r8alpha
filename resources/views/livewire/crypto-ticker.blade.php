<div wire:poll.10000ms="fetchTickers" class="h-7 sm:h-8 bg-gray-900/50 border-t border-gray-800/30 overflow-hidden">
    <style>
        .text-success-400 { color: #4ade80; } 
        .text-danger-400 { color: #f87171; } 
        
    </style>


    <div class="flex items-center h-full ticker-tape whitespace-nowrap overflow-x-scroll lg:overflow-x-visible">
        
        {{-- Grupo de tickers 1 (Itens originais) --}}
        <div class="flex items-center space-x-4 sm:space-x-6 md:space-x-8 px-2 sm:px-4 flex-shrink-0">

            @foreach ($tickers as $index => $ticker)
                
                @php
                    $visibilityClasses = match ($index) {
                        0, 1, 2, 3 => 'flex', 
                        default => 'hidden lg:flex', 
                    };

                    $priceColor = $ticker['is_positive'] ? 'text-success-400' : 'text-danger-400';
                    $sign = $ticker['is_positive'] ? '+' : '-';
                    $pulseClass = 'price-pulse-' . $index;
                @endphp

                <div class="{{ $visibilityClasses }} items-center space-x-1 sm:space-x-2" 
                     wire:key="ticker-{{ $index }}">
                    
                    <span class="text-xs text-gray-400 whitespace-nowrap">{{ $ticker['symbol'] }}</span>
                    
                   
                    <span class="text-xs sm:text-sm font-semibold {{ $priceColor }} {{ $pulseClass }}">
                        @if ($ticker['price'] !== 'N/A')
                            ${{ $ticker['price'] }}
                        @else
                            {{ $ticker['price'] }}
                        @endif
                    </span>
                    
                   
                    <span class="text-xs {{ $priceColor }}">
                        @if ($ticker['percent'] !== 'N/A')
                            {{ $sign }}{{ $ticker['percent'] }}
                        @else
                            {{ $ticker['percent'] }}
                        @endif
                    </span>

                </div>
            @endforeach
        </div>


        <div class="hidden lg:flex items-center space-x-4 sm:space-x-6 md:space-x-8 px-2 sm:px-4 flex-shrink-0">
            @foreach ($tickers as $index => $ticker)
                
                @php
                    $visibilityClasses = match ($index) {
                        0, 1, 2, 3 => 'flex',
                        default => 'hidden lg:flex',
                    };
                    $priceColor = $ticker['is_positive'] ? 'text-success-400' : 'text-danger-400';
                    $sign = $ticker['is_positive'] ? '+' : '-';
                @endphp
                
                {{-- Usa um 'wire:key' diferente para o grupo duplicado --}}
                <div class="{{ $visibilityClasses }} items-center space-x-1 sm:space-x-2" 
                     wire:key="ticker-duplicate-{{ $index }}">
                    
                    <span class="text-xs text-gray-400 whitespace-nowrap">{{ $ticker['symbol'] }}</span>
                    
                    
                    <span class="text-xs sm:text-sm font-semibold {{ $priceColor }}">
                        @if ($ticker['price'] !== 'N/A')
                            ${{ $ticker['price'] }}
                        @else
                            {{ $ticker['price'] }}
                        @endif
                    </span>
                    
                   
                    <span class="text-xs {{ $priceColor }}">
                        @if ($ticker['percent'] !== 'N/A')
                            {{ $sign }}{{ $ticker['percent'] }}
                        @else
                            {{ $ticker['percent'] }}
                        @endif
                    </span>
                </div>
            @endforeach
        </div>
        
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            // Monitora a mudança na propriedade `tickers` do componente Livewire
            Livewire.hook('element.updated', ({ el, component }) => {
                if (el.dataset.livewireId === component.id && component.name === 'crypto-ticker') {
                    const newTickers = component.get('tickers');
                    
                    // Compara os novos dados com os dados anteriores (se existirem)
                    if (component.previousTickers) {
                        newTickers.forEach((newTicker, index) => {
                            const oldTicker = component.previousTickers[index];
                            
                            // Verifica se o preço mudou (ignorando formatação, por isso 'raw_price')
                            if (oldTicker && newTicker.raw_price !== oldTicker.raw_price) {
                                const priceElement = el.querySelector(`.price-pulse-${index}`);
                                if (priceElement) {
                                    // Remove qualquer animação anterior
                                    priceElement.classList.remove('animate-pulse-success', 'animate-pulse-danger');

                                    // Adiciona a animação de pulso com base na direção da mudança
                                    if (newTicker.raw_price > oldTicker.raw_price) {
                                        priceElement.classList.add('animate-pulse-success');
                                    } else {
                                        priceElement.classList.add('animate-pulse-danger');
                                    }
                                    
                                    // Remove a classe após a animação (1 segundo)
                                    setTimeout(() => {
                                        priceElement.classList.remove('animate-pulse-success', 'animate-pulse-danger');
                                    }, 1000);
                                }
                            }
                        });
                    }
                    // Armazena os dados atuais para a próxima comparação
                    component.previousTickers = newTickers;
                }
            });
        });
    </script>
</div>