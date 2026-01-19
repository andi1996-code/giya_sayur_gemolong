<!-- Member Section -->
<div class="space-y-3">
    <div class="flex items-center justify-between">
        <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-white">
            Member
        </label>
        @if($selected_member)
            <button wire:click="clearMember" type="button"
                class="text-xs text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                Hapus Member
            </button>
        @endif
    </div>

    @if(!$selected_member)
        <!-- Member Search Form -->
        <div class="flex gap-2">
            <input type="text" wire:model="member_code"
                placeholder="Kode Member / No. Telp"
                class="flex-1 px-3 sm:px-4 py-2 sm:py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                wire:keydown.enter="searchMember">
            <button wire:click="searchMember" type="button"
                class="px-4 py-2 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 flex items-center gap-2 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </button>
        </div>
    @else
        <!-- Member Info Card -->
        <div class="bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/30 dark:to-pink-900/30 rounded-xl p-3 sm:p-4 border-2 border-purple-200 dark:border-purple-700">
            <!-- Member Header -->
            <div class="flex items-start justify-between mb-3">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <h4 class="text-sm font-bold text-purple-900 dark:text-purple-200">
                            {{ $selected_member->name }}
                        </h4>
                    </div>
                    <p class="text-xs text-purple-700 dark:text-purple-300">
                        {{ $selected_member->member_code }}
                    </p>
                </div>

                <!-- Tier Badge -->
                <span class="px-2 py-1 text-xs font-bold rounded-lg
                    @if($selected_member->tier === 'bronze') bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-300
                    @elseif($selected_member->tier === 'silver') bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300
                    @elseif($selected_member->tier === 'gold') bg-yellow-100 text-yellow-700 dark:bg-yellow-900/50 dark:text-yellow-300
                    @elseif($selected_member->tier === 'platinum') bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300
                    @endif">
                    {{ strtoupper($selected_member->tier) }}
                </span>
            </div>

            <!-- Points Info -->
            <div class="bg-white/60 dark:bg-gray-800/60 rounded-lg p-2 sm:p-3 mb-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-600 dark:text-gray-400">Poin Tersedia</span>
                    <span class="text-lg font-bold text-purple-700 dark:text-purple-300">
                        {{ number_format($selected_member->total_points, 0, ',', '.') }}
                    </span>
                </div>
                <div class="flex items-center justify-between mt-1">
                    <span class="text-xs text-gray-600 dark:text-gray-400">Multiplier</span>
                    <span class="text-sm font-semibold text-purple-600 dark:text-purple-400">
                        {{ $selected_member->getTierMultiplier() }}x
                    </span>
                </div>
            </div>

            <!-- Points Redemption -->
            @php
                $pointSettings = \App\Models\PointSetting::first();
                $canRedeem = $pointSettings && $selected_member->total_points >= $pointSettings->min_points_redeem;
            @endphp

            @if($canRedeem && $total_price > 0)
                <div class="space-y-2">
                    <label class="block text-xs font-medium text-purple-900 dark:text-purple-200">
                        Tukar Poin (1 poin = Rp {{ number_format($pointSettings->point_value, 0, ',', '.') }})
                    </label>

                    <div class="flex gap-2">
                        <input type="number" wire:model.live="points_to_redeem"
                            min="0"
                            max="{{ $this->maxPointsRedeem }}"
                            placeholder="0"
                            class="flex-1 px-3 py-2 rounded-lg border border-purple-300 dark:border-purple-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">

                        @if($this->maxPointsRedeem > 0)
                            <button wire:click="useMaxPoints" type="button"
                                class="px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs rounded-lg transition-colors">
                                Max
                            </button>
                        @endif
                    </div>

                    @if($points_to_redeem > 0)
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-purple-700 dark:text-purple-300">Diskon Poin:</span>
                            <span class="font-bold text-green-600 dark:text-green-400">
                                - Rp {{ number_format($points_discount, 0, ',', '.') }}
                            </span>
                        </div>
                    @endif

                    <p class="text-xs text-gray-600 dark:text-gray-400">
                        Max: {{ number_format($this->maxPointsRedeem, 0, ',', '.') }} poin
                    </p>
                </div>
            @elseif($pointSettings && $selected_member->total_points < $pointSettings->min_points_redeem)
                <div class="text-xs text-center p-2 bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400 rounded-lg">
                    Minimal {{ $pointSettings->min_points_redeem }} poin untuk ditukar
                </div>
            @endif
        </div>
    @endif
</div>
