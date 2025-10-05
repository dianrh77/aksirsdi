@extends('layouts.master')
@section('content')
    <div class="animate__animated p-6" :class="[$store.app.animation]">
        <!-- start main content section -->
        <div x-data="crypto">
            <ul class="flex space-x-2 rtl:space-x-reverse">
                <li>
                    <a href="javascript:;" class="text-primary hover:underline">Dashboard</a>
                </li>
                <li class="before:content-['/'] ltr:before:mr-1 rtl:before:ml-1">
                    <span>Crypto</span>
                </li>
            </ul>
            <div class="relative mt-5 flex flex-col gap-5 xl:flex-row">
                <div class="panel perfect-scrollbar absolute z-10 hidden w-80 flex-none divide-y divide-[#ebedf2] overflow-visible border-0 p-0 dark:divide-[#191e3a] lg:overflow-y-auto xl:relative xl:block" :class="{'!block h-full' : isShowCryptoMenu}">
                    <template x-for="item in coins">
                        <button type="button" class="flex w-full items-center p-4 hover:bg-gray-100 dark:hover:bg-[#192A3A]" :class="{'bg-gray-100 dark:bg-[#192A3A]' : selectedCoinObj.title === item.title}" @click="selectBitCoin(item)">
                            <div class="ltr:pr-4 rtl:pl-4">
                                <div class="flex items-baseline font-semibold">
                                    <div class="text-md ltr:mr-1 rtl:ml-1" x-text="item.title"></div>
                                    <div class="text-xs uppercase text-white-dark" x-text="`(${item.alias})`"></div>
                                </div>
                                <div class="mt-2 flex items-center" :class="[item.isUp ? 'text-success' : 'text-danger']">
                                    <div class="min-w-20 text-xl ltr:mr-3 rtl:ml-3" x-text="`$${item.value}`"></div>
                                    <span>
                                        <svg width="24" height="24" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="{'rotate-180' : item.isUp}">
                                            <path d="M12 4L12 20M12 20L18 14M12 20L6 14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                    </span>
                                    <div class="text-sm font-medium" x-text="`${item.perc}%`"></div>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div :id="item.title.toLowerCase()" class="chart overflow-hidden"></div>
                            </div>
                        </button>
                    </template>
                </div>

                <div class="absolute z-[5] hidden h-full w-full rounded-md bg-black/60" :class="{'!block xl:!hidden' : isShowCryptoMenu}" @click="isShowCryptoMenu = !isShowCryptoMenu"></div>

                <div class="panel flex-1 p-0">
                    <div class="flex-wrap items-center border-b border-[#ebedf2] p-4 dark:border-[#191e3a] md:flex">
                        <div class="flex flex-1 items-start ltr:pr-4 rtl:pl-4">
                            <button type="button" class="block hover:text-primary ltr:mr-5 rtl:ml-5 xl:hidden" @click="isShowCryptoMenu = !isShowCryptoMenu">
                                <svg width="24" height="24" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6">
                                    <path d="M20 7L4 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                    <path opacity="0.5" d="M20 12L4 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                    <path d="M20 17L4 17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                </svg>
                            </button>
                            <div>
                                <div class="flex items-center">
                                    <div class="text-md font-semibold ltr:mr-1 rtl:ml-1" x-text="selectedCoinObj.title"></div>
                                    <div class="text-xs font-semibold uppercase text-white-dark" x-text="`(${selectedCoinObj.alias})`"></div>
                                </div>
                                <div class="mt-2 flex items-center" :class="selectedCoinObj.isUp ? 'text-success' : 'text-danger'">
                                    <div class="min-w-20 text-2xl ltr:mr-3 rtl:ml-3" x-text="`$${selectedCoinObj.value}`"></div>
                                    <svg width="24" height="24" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="{'rotate-180' : selectedCoinObj.isUp}">
                                        <path d="M12 4L12 20M12 20L18 14M12 20L6 14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                    <div class="mb-px text-sm font-medium" x-text="`${selectedCoinObj.perc}%`"></div>
                                </div>
                            </div>
                        </div>
                        <ul class="mt-5 grid grid-cols-2 divide-[#ebedf2] font-semibold text-white-dark rtl:divide-x-reverse dark:divide-[#253b5c] sm:mt-0 sm:grid-cols-4 sm:divide-x ltr:md:ml-auto rtl:md:mr-auto">
                            <li class="px-4 py-1">
                                Market Cap
                                <span class="mt-1.5 block text-lg text-black dark:text-white-light" x-text="`$${selectedCoinObj.marketcap}B`"></span>
                            </li>
                            <li class="px-4 py-1">
                                Volume
                                <span class="mt-1.5 block text-lg text-black dark:text-white-light" x-text="`$${selectedCoinObj.volume}B`"> </span>
                            </li>
                            <li class="px-4 py-1">
                                Supply
                                <span class="mt-1.5 block text-lg text-black dark:text-white-light" x-text="`${selectedCoinObj.supply}M`"></span>
                            </li>
                            <li class="px-4 py-1">
                                All Time High
                                <span class="mt-1.5 block text-lg text-black dark:text-white-light" x-text="`$${selectedCoinObj.highest}`"></span>
                            </li>
                        </ul>
                    </div>
                    <div class="mb-5 grid grid-rows-1 gap-4 border-b border-[#ebedf2] p-4 dark:border-[#253b5c] sm:grid-cols-4">
                        <div>
                            <div class="mb-1.5 font-semibold">Action</div>
                            <select class="form-select text-white-dark">
                                <option>Buy</option>
                                <option>Sell</option>
                            </select>
                        </div>
                        <div>
                            <div class="mb-1.5 font-semibold">Wallet</div>
                            <select class="form-select text-white-dark">
                                <option>Bitcoin</option>
                                <option>Ethereum</option>
                                <option>Zcash</option>
                                <option>Litecoin</option>
                                <option>Binance</option>
                                <option>Solana</option>
                                <option>Tether</option>
                            </select>
                        </div>
                        <div>
                            <div class="mb-1.5 font-semibold">Amount</div>
                            <div class="relative flex">
                                <input type="text" placeholder="" class="form-input ltr:rounded-r-none ltr:border-r-0 rtl:rounded-l-none rtl:border-l-0">
                                <div x-data="dropdown" @click.outside="open = false" class="dropdown">
                                    <div class="flex cursor-pointer items-center justify-center gap-1 rounded-none border bg-[#f1f2f3] px-3 py-2 font-semibold ltr:rounded-r-md rtl:rounded-l-md dark:border-[#253b5c] dark:bg-[#1b2e4b]" @click="toggle">
                                        BTC

                                        <svg width="24" height="24" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4">
                                            <path d="M19 9L12 15L5 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                    </div>
                                    <ul x-cloak="" x-show="open" x-transition="" x-transition.duration.300ms="" class="ltr:right-0 rtl:left-0">
                                        <li><a href="javascript:;" @click="toggle">BTC</a></li>
                                        <li><a href="javascript:;" @click="toggle">ETH</a></li>
                                        <li><a href="javascript:;" @click="toggle">ZEC</a></li>
                                        <li><a href="javascript:;" @click="toggle">LTC</a></li>
                                        <li><a href="javascript:;" @click="toggle">BNB</a></li>
                                        <li><a href="javascript:;" @click="toggle">SOL</a></li>
                                        <li><a href="javascript:;" @click="toggle">USDT</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary self-end">Buy</button>
                    </div>
                    <!-- selected chart -->
                    <div class="flex-1 px-4 overflow-hidden">
                        <div x-ref="selectedBitCoin"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end main content section -->
    </div>
    @section('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                // main section
                Alpine.data('scrollToTop', () => ({
                    showTopButton: false,
                    init() {
                        window.onscroll = () => {
                            this.scrollFunction();
                        };
                    },

                    scrollFunction() {
                        if (document.body.scrollTop > 50 || document.documentElement.scrollTop > 50) {
                            this.showTopButton = true;
                        } else {
                            this.showTopButton = false;
                        }
                    },

                    goToTop() {
                        document.body.scrollTop = 0;
                        document.documentElement.scrollTop = 0;
                    },
                }));

                //crypto
                Alpine.data('crypto', () => ({
                    isShowCryptoMenu: false,
                    coins: [
                        {
                            id: 1,
                            title: 'Bitcoin',
                            alias: 'BTC',
                            value: 170.46,
                            perc: 2.35,
                            marketcap: 148.75,
                            volume: 22.9,
                            supply: 18.17,
                            highest: '19,891.00',
                            isUp: true,
                            series: [
                                {
                                    x: new Date(1538778600000),
                                    y: [6629.81, 6650.5, 6623.04, 6633.33],
                                },
                                {
                                    x: new Date(1538780400000),
                                    y: [6632.01, 6643.59, 6620, 6630.11],
                                },
                                {
                                    x: new Date(1538782200000),
                                    y: [6630.71, 6648.95, 6623.34, 6635.65],
                                },
                                {
                                    x: new Date(1538784000000),
                                    y: [6635.65, 6651, 6629.67, 6638.24],
                                },
                                {
                                    x: new Date(1538785800000),
                                    y: [6638.24, 6640, 6620, 6624.47],
                                },
                                {
                                    x: new Date(1538787600000),
                                    y: [6624.53, 6636.03, 6621.68, 6624.31],
                                },
                                {
                                    x: new Date(1538789400000),
                                    y: [6624.61, 6632.2, 6617, 6626.02],
                                },
                                {
                                    x: new Date(1538791200000),
                                    y: [6627, 6627.62, 6584.22, 6603.02],
                                },
                                {
                                    x: new Date(1538793000000),
                                    y: [6605, 6608.03, 6598.95, 6604.01],
                                },
                                {
                                    x: new Date(1538794800000),
                                    y: [6604.5, 6614.4, 6602.26, 6608.02],
                                },
                                {
                                    x: new Date(1538796600000),
                                    y: [6608.02, 6610.68, 6601.99, 6608.91],
                                },
                                {
                                    x: new Date(1538798400000),
                                    y: [6608.91, 6618.99, 6608.01, 6612],
                                },
                                {
                                    x: new Date(1538800200000),
                                    y: [6612, 6615.13, 6605.09, 6612],
                                },
                                {
                                    x: new Date(1538802000000),
                                    y: [6612, 6624.12, 6608.43, 6622.95],
                                },
                                {
                                    x: new Date(1538803800000),
                                    y: [6623.91, 6623.91, 6615, 6615.67],
                                },
                                {
                                    x: new Date(1538805600000),
                                    y: [6618.69, 6618.74, 6610, 6610.4],
                                },
                                {
                                    x: new Date(1538807400000),
                                    y: [6611, 6622.78, 6610.4, 6614.9],
                                },
                                {
                                    x: new Date(1538809200000),
                                    y: [6614.9, 6626.2, 6613.33, 6623.45],
                                },
                                {
                                    x: new Date(1538811000000),
                                    y: [6623.48, 6627, 6618.38, 6620.35],
                                },
                                {
                                    x: new Date(1538812800000),
                                    y: [6619.43, 6620.35, 6610.05, 6615.53],
                                },
                                {
                                    x: new Date(1538814600000),
                                    y: [6615.53, 6617.93, 6610, 6615.19],
                                },
                                {
                                    x: new Date(1538816400000),
                                    y: [6615.19, 6621.6, 6608.2, 6620],
                                },
                                {
                                    x: new Date(1538818200000),
                                    y: [6619.54, 6625.17, 6614.15, 6620],
                                },
                                {
                                    x: new Date(1538820000000),
                                    y: [6620.33, 6634.15, 6617.24, 6624.61],
                                },
                                {
                                    x: new Date(1538821800000),
                                    y: [6625.95, 6626, 6611.66, 6617.58],
                                },
                            ],
                        },
                        {
                            id: 2,
                            title: 'Ethereum',
                            alias: 'ETH',
                            value: 140.67,
                            perc: 1.24,
                            marketcap: 130.89,
                            volume: 32.0,
                            supply: 23.56,
                            highest: '18,568.23',
                            isUp: false,
                            series: [
                                {
                                    x: new Date(1538778600000),
                                    y: [6624.61, 6632.2, 6617, 6626.02],
                                },
                                {
                                    x: new Date(1538780400000),
                                    y: [6627, 6627.62, 6584.22, 6603.02],
                                },
                                {
                                    x: new Date(1538782200000),
                                    y: [6605, 6608.03, 6598.95, 6604.01],
                                },
                                {
                                    x: new Date(1538784000000),
                                    y: [6635.65, 6651, 6629.67, 6638.24],
                                },
                                {
                                    x: new Date(1538785800000),
                                    y: [6638.24, 6640, 6620, 6624.47],
                                },
                                {
                                    x: new Date(1538787600000),
                                    y: [6612, 6615.13, 6605.09, 6612],
                                },
                                {
                                    x: new Date(1538789400000),
                                    y: [6612, 6624.12, 6608.43, 6622.95],
                                },
                                {
                                    x: new Date(1538791200000),
                                    y: [6623.91, 6623.91, 6615, 6615.67],
                                },
                                {
                                    x: new Date(1538793000000),
                                    y: [6618.69, 6618.74, 6610, 6610.4],
                                },
                                {
                                    x: new Date(1538794800000),
                                    y: [6611, 6622.78, 6610.4, 6614.9],
                                },
                                {
                                    x: new Date(1538796600000),
                                    y: [6600.55, 6605, 6589.14, 6593.01],
                                },
                                {
                                    x: new Date(1538798400000),
                                    y: [6593.15, 6605, 6592, 6603.06],
                                },
                                {
                                    x: new Date(1538800200000),
                                    y: [6603.07, 6604.5, 6599.09, 6603.89],
                                },
                                {
                                    x: new Date(1538802000000),
                                    y: [6604.44, 6604.44, 6600, 6603.5],
                                },
                                {
                                    x: new Date(1538803800000),
                                    y: [6603.5, 6603.99, 6597.5, 6603.86],
                                },
                                {
                                    x: new Date(1538805600000),
                                    y: [6635.65, 6651, 6629.67, 6638.24],
                                },
                                {
                                    x: new Date(1538807400000),
                                    y: [6638.24, 6640, 6620, 6624.47],
                                },
                                {
                                    x: new Date(1538809200000),
                                    y: [6612, 6615.13, 6605.09, 6612],
                                },
                                {
                                    x: new Date(1538811000000),
                                    y: [6612, 6624.12, 6608.43, 6622.95],
                                },
                            ],
                        },
                        {
                            id: 3,
                            title: 'Zcash',
                            alias: 'ZEC',
                            value: 58.41,
                            perc: 1.35,
                            marketcap: 150.26,
                            volume: 22.23,
                            supply: 12.25,
                            highest: '19,256.35',
                            isUp: true,
                            series: [
                                {
                                    x: new Date(1538778600000),
                                    y: [6623.91, 6623.91, 6615, 6615.67],
                                },
                                {
                                    x: new Date(1538780400000),
                                    y: [6618.69, 6618.74, 6610, 6610.4],
                                },
                                {
                                    x: new Date(1538782200000),
                                    y: [6611, 6622.78, 6610.4, 6614.9],
                                },
                                {
                                    x: new Date(1538784000000),
                                    y: [6614.9, 6626.2, 6613.33, 6623.45],
                                },
                                {
                                    x: new Date(1538785800000),
                                    y: [6623.48, 6627, 6618.38, 6620.35],
                                },
                                {
                                    x: new Date(1538787600000),
                                    y: [6619.43, 6620.35, 6610.05, 6615.53],
                                },
                                {
                                    x: new Date(1538789400000),
                                    y: [6615.53, 6617.93, 6610, 6615.19],
                                },
                                {
                                    x: new Date(1538791200000),
                                    y: [6615.19, 6621.6, 6608.2, 6620],
                                },
                                {
                                    x: new Date(1538793000000),
                                    y: [6619.54, 6625.17, 6614.15, 6620],
                                },
                                {
                                    x: new Date(1538794800000),
                                    y: [6620.33, 6634.15, 6617.24, 6624.61],
                                },
                                {
                                    x: new Date(1538796600000),
                                    y: [6625.95, 6626, 6611.66, 6617.58],
                                },
                                {
                                    x: new Date(1538798400000),
                                    y: [6619, 6625.97, 6595.27, 6598.86],
                                },
                            ],
                        },
                        {
                            id: 4,
                            title: 'Litecoin',
                            alias: 'LTC',
                            value: 180.36,
                            perc: 2.0,
                            marketcap: 150.36,
                            volume: 24.25,
                            supply: 15.5,
                            highest: '18,056.00',
                            isUp: false,
                            series: [
                                {
                                    x: new Date(1538778600000),
                                    y: [6598.03, 6600, 6588.73, 6595.97],
                                },
                                {
                                    x: new Date(1538780400000),
                                    y: [6595.97, 6602.01, 6588.17, 6602],
                                },
                                {
                                    x: new Date(1538782200000),
                                    y: [6602, 6607, 6596.51, 6599.95],
                                },
                                {
                                    x: new Date(1538784000000),
                                    y: [6600.63, 6601.21, 6590.39, 6591.02],
                                },
                                {
                                    x: new Date(1538785800000),
                                    y: [6591.02, 6603.08, 6591, 6591],
                                },
                                {
                                    x: new Date(1538787600000),
                                    y: [6591, 6601.32, 6585, 6592],
                                },
                                {
                                    x: new Date(1538789400000),
                                    y: [6593.13, 6596.01, 6610, 6608.34],
                                },
                                {
                                    x: new Date(1538791200000),
                                    y: [6612.34, 6604.76, 6582.63, 6593.86],
                                },
                                {
                                    x: new Date(1538793000000),
                                    y: [6593.86, 6604.28, 6586.57, 6600.01],
                                },
                                {
                                    x: new Date(1538794800000),
                                    y: [6601.81, 6603.21, 6592.78, 6596.25],
                                },
                                {
                                    x: new Date(1538796600000),
                                    y: [6596.25, 6604.2, 6590, 6602.99],
                                },
                                {
                                    x: new Date(1538798400000),
                                    y: [6602.99, 6606, 6584.99, 6587.81],
                                },
                                {
                                    x: new Date(1538800200000),
                                    y: [6587.81, 6595, 6583.27, 6591.96],
                                },
                                {
                                    x: new Date(1538802000000),
                                    y: [6591.97, 6596.07, 6585, 6588.39],
                                },
                                {
                                    x: new Date(1538803800000),
                                    y: [6587.6, 6598.21, 6587.6, 6594.27],
                                },
                                {
                                    x: new Date(1538805600000),
                                    y: [6596.44, 6601, 6590, 6596.55],
                                },
                            ],
                        },
                        {
                            id: 5,
                            title: 'Binance',
                            alias: 'BNB',
                            value: 160.23,
                            perc: 1.23,
                            marketcap: 180.36,
                            volume: 20.2,
                            supply: 22.5,
                            highest: '19,500.00',
                            isUp: true,
                            series: [
                                {
                                    x: new Date(1538778600000),
                                    y: [6612.34, 6604.76, 6582.63, 6593.86],
                                },
                                {
                                    x: new Date(1538780400000),
                                    y: [6593.86, 6604.28, 6586.57, 6600.01],
                                },
                                {
                                    x: new Date(1538782200000),
                                    y: [6601.81, 6603.21, 6592.78, 6596.25],
                                },
                                {
                                    x: new Date(1538784000000),
                                    y: [6596.25, 6604.2, 6590, 6602.99],
                                },
                                {
                                    x: new Date(1538785800000),
                                    y: [6602.99, 6606, 6584.99, 6587.81],
                                },
                                {
                                    x: new Date(1538787600000),
                                    y: [6587.81, 6595, 6583.27, 6591.96],
                                },
                                {
                                    x: new Date(1538789400000),
                                    y: [6591.97, 6596.07, 6585, 6588.39],
                                },
                                {
                                    x: new Date(1538791200000),
                                    y: [6587.6, 6598.21, 6587.6, 6594.27],
                                },
                                {
                                    x: new Date(1538793000000),
                                    y: [6596.44, 6601, 6590, 6596.55],
                                },
                                {
                                    x: new Date(1538794800000),
                                    y: [6598.91, 6605, 6596.61, 6600.02],
                                },
                                {
                                    x: new Date(1538796600000),
                                    y: [6600.55, 6605, 6589.14, 6593.01],
                                },
                            ],
                        },
                        {
                            id: 6,
                            title: 'Solana',
                            alias: 'SOL',
                            value: 150.89,
                            perc: 2.0,
                            marketcap: 180.45,
                            volume: 23.0,
                            supply: 20.22,
                            highest: '18059.50',
                            isUp: false,
                            series: [
                                {
                                    x: new Date(1538778600000),
                                    y: [6596.25, 6604.2, 6590, 6602.99],
                                },
                                {
                                    x: new Date(1538780400000),
                                    y: [6602.99, 6606, 6584.99, 6587.81],
                                },
                                {
                                    x: new Date(1538782200000),
                                    y: [6587.81, 6595, 6583.27, 6591.96],
                                },
                                {
                                    x: new Date(1538784000000),
                                    y: [6591.97, 6596.07, 6585, 6588.39],
                                },
                                {
                                    x: new Date(1538785800000),
                                    y: [6587.6, 6598.21, 6587.6, 6594.27],
                                },
                                {
                                    x: new Date(1538787600000),
                                    y: [6593.15, 6605, 6592, 6603.06],
                                },
                                {
                                    x: new Date(1538789400000),
                                    y: [6608.91, 6618.99, 6608.01, 6612],
                                },
                                {
                                    x: new Date(1538789400000),
                                    y: [6635.65, 6651, 6629.67, 6638.24],
                                },
                                {
                                    x: new Date(1538793000000),
                                    y: [6638.24, 6640, 6620, 6624.47],
                                },
                                {
                                    x: new Date(1538794800000),
                                    y: [6612, 6615.13, 6605.09, 6612],
                                },
                                {
                                    x: new Date(1538796600000),
                                    y: [6612, 6624.12, 6608.43, 6622.95],
                                },
                                {
                                    x: new Date(1538798400000),
                                    y: [6623.91, 6623.91, 6615, 6615.67],
                                },
                                {
                                    x: new Date(1538800200000),
                                    y: [6603.5, 6603.99, 6597.5, 6603.86],
                                },
                                {
                                    x: new Date(1538802000000),
                                    y: [6603.85, 6605, 6600, 6604.07],
                                },
                                {
                                    x: new Date(1538803800000),
                                    y: [6604.98, 6606, 6604.07, 6606],
                                },
                            ],
                        },
                        {
                            id: 7,
                            title: 'Tether',
                            alias: 'USDT',
                            value: 170.84,
                            perc: 1.9,
                            marketcap: 185.25,
                            volume: 25.25,
                            supply: 20.47,
                            highest: '19,800.00',
                            isUp: true,
                            series: [
                                {
                                    x: new Date(1538778600000),
                                    y: [6635.65, 6651, 6629.67, 6638.24],
                                },
                                {
                                    x: new Date(1538780400000),
                                    y: [6638.24, 6640, 6620, 6624.47],
                                },
                                {
                                    x: new Date(1538782200000),
                                    y: [6612, 6615.13, 6605.09, 6612],
                                },
                                {
                                    x: new Date(1538784000000),
                                    y: [6612, 6624.12, 6608.43, 6622.95],
                                },
                                {
                                    x: new Date(1538785800000),
                                    y: [6623.91, 6623.91, 6615, 6615.67],
                                },
                                {
                                    x: new Date(1538787600000),
                                    y: [6618.69, 6618.74, 6610, 6610.4],
                                },
                                {
                                    x: new Date(1538789400000),
                                    y: [6611, 6622.78, 6610.4, 6614.9],
                                },
                                {
                                    x: new Date(1538791200000),
                                    y: [6614.9, 6626.2, 6613.33, 6623.45],
                                },
                                {
                                    x: new Date(1538793000000),
                                    y: [6623.48, 6627, 6618.38, 6620.35],
                                },
                                {
                                    x: new Date(1538794800000),
                                    y: [6619.43, 6620.35, 6610.05, 6615.53],
                                },
                                {
                                    x: new Date(1538796600000),
                                    y: [6638.24, 6640, 6620, 6624.47],
                                },
                                {
                                    x: new Date(1538798400000),
                                    y: [6624.53, 6636.03, 6621.68, 6624.31],
                                },
                                {
                                    x: new Date(1538800200000),
                                    y: [6624.61, 6632.2, 6617, 6626.02],
                                },
                                {
                                    x: new Date(1538802000000),
                                    y: [6627, 6627.62, 6584.22, 6603.02],
                                },
                                {
                                    x: new Date(1538803800000),
                                    y: [6615.53, 6617.93, 6610, 6615.19],
                                },
                            ],
                        },
                    ],

                    init() {
                        isDark = this.$store.app.theme === 'dark' || this.$store.app.isDarkMode ? true : false;
                        isRtl = this.$store.app.rtlClass === 'rtl' ? true : false;
                        let selectedCoinObj = null;
                        let selectedBitCoin = null;
                        const bitcoin = null;
                        const zcash = null;
                        const ethereum = null;
                        const litecoin = null;
                        const binance = null;
                        const solana = null;
                        const tether = null;
                        this.selectedCoinObj = this.coins[0];

                        setTimeout(() => {
                            this.bitcoin = new ApexCharts(document.querySelector('#bitcoin'), this.bitcoinOptions);
                            this.bitcoin.render();

                            this.zcash = new ApexCharts(document.querySelector('#zcash'), this.zcashOptions);
                            this.zcash.render();

                            this.ethereum = new ApexCharts(document.querySelector('#ethereum'), this.ethereumOptions);
                            this.ethereum.render();

                            this.litecoin = new ApexCharts(document.querySelector('#litecoin'), this.litecoinOptions);
                            this.litecoin.render();

                            this.binance = new ApexCharts(document.querySelector('#binance'), this.binanceOptions);
                            this.binance.render();

                            this.solana = new ApexCharts(document.querySelector('#solana'), this.solanaOptions);
                            this.solana.render();

                            this.tether = new ApexCharts(document.querySelector('#tether'), this.tetherOptions);
                            this.tether.render();

                            this.selectedBitCoin = new ApexCharts(this.$refs.selectedBitCoin, this.selectedBitCoinOptions);
                            this.selectedBitCoin.render();
                        }, 200);

                        this.$watch('$store.app.theme', () => {
                            isDark = this.$store.app.theme === 'dark' || this.$store.app.isDarkMode ? true : false;
                            this.bitcoin.updateOptions(this.bitcoinOptions);
                            this.zcash.updateOptions(this.zcashOptions);
                            this.ethereum.updateOptions(this.ethereumOptions);
                            this.litecoin.updateOptions(this.litecoinOptions);
                            this.binance.updateOptions(this.binanceOptions);
                            this.solana.updateOptions(this.solanaOptions);
                            this.tether.updateOptions(this.tetherOptions);
                            this.selectedBitCoin.updateOptions(this.selectedBitCoinOptions);
                        });

                        this.$watch('$store.app.rtlClass', () => {
                            isRtl = this.$store.app.rtlClass === 'rtl' ? true : false;
                            this.selectedBitCoin.updateOptions(this.selectedBitCoinOptions);
                        });

                        this.$watch('$store.app.layout', () => {
                            this.selectedBitCoin.updateOptions(this.selectedBitCoinOptions);
                        });

                        this.$watch('$store.app.layout', () => {
                            this.selectedBitCoin.updateOptions(this.selectedBitCoinOptions);
                        });

                        this.$watch('$store.app.sidebar', () => {
                            setTimeout(() => {
                                this.selectedBitCoin.updateOptions(this.selectedBitCoinOptions);
                            }, 300);
                        });
                    },

                    get bitcoinOptions() {
                        return {
                            series: [
                                {
                                    data: this.coins[this.coins.findIndex((d) => d.title === 'Bitcoin')].series,
                                },
                            ],
                            chart: {
                                height: 45,
                                width: 120,
                                type: 'line',
                                sparkline: {
                                    enabled: true,
                                },
                            },
                            stroke: {
                                width: 2,
                            },
                            markers: {
                                size: 0,
                            },
                            colors: ['#00ab55'],
                            grid: {
                                padding: {
                                    top: 0,
                                    bottom: 0,
                                    left: 0,
                                },
                            },
                            tooltip: {
                                x: {
                                    show: false,
                                },
                                y: {
                                    title: {
                                        formatter: (formatter = () => {
                                            return '';
                                        }),
                                    },
                                },
                            },
                            responsive: [
                                {
                                    breakPoint: 576,
                                    options: {
                                        chart: {
                                            height: 95,
                                        },
                                        grid: {
                                            padding: {
                                                top: 45,
                                                bottom: 0,
                                                left: 0,
                                            },
                                        },
                                    },
                                },
                            ],
                        };
                    },

                    get zcashOptions() {
                        return {
                            series: [
                                {
                                    data: this.coins[this.coins.findIndex((d) => d.title === 'Zcash')].series,
                                },
                            ],
                            chart: {
                                height: 45,
                                width: 120,
                                type: 'line',
                                sparkline: {
                                    enabled: true,
                                },
                            },
                            stroke: {
                                width: 2,
                            },
                            markers: {
                                size: 0,
                            },
                            colors: ['#00ab55'],
                            grid: {
                                padding: {
                                    top: 0,
                                    bottom: 0,
                                    left: 0,
                                },
                            },
                            tooltip: {
                                x: {
                                    show: false,
                                },
                                y: {
                                    title: {
                                        formatter: (formatter = () => {
                                            return '';
                                        }),
                                    },
                                },
                            },
                            responsive: [
                                {
                                    breakPoint: 576,
                                    options: {
                                        chart: {
                                            height: 95,
                                        },
                                        grid: {
                                            padding: {
                                                top: 45,
                                                bottom: 0,
                                                left: 0,
                                            },
                                        },
                                    },
                                },
                            ],
                        };
                    },

                    get ethereumOptions() {
                        return {
                            series: [
                                {
                                    data: this.coins[this.coins.findIndex((d) => d.title === 'Ethereum')].series,
                                },
                            ],
                            chart: {
                                height: 45,
                                width: 120,
                                type: 'line',
                                sparkline: {
                                    enabled: true,
                                },
                            },
                            stroke: {
                                width: 2,
                            },
                            markers: {
                                size: 0,
                            },
                            colors: ['#e7515a'],
                            grid: {
                                padding: {
                                    top: 0,
                                    bottom: 0,
                                    left: 0,
                                },
                            },
                            tooltip: {
                                x: {
                                    show: false,
                                },
                                y: {
                                    title: {
                                        formatter: (formatter = () => {
                                            return '';
                                        }),
                                    },
                                },
                            },
                            responsive: [
                                {
                                    breakPoint: 576,
                                    options: {
                                        chart: {
                                            height: 95,
                                        },
                                        grid: {
                                            padding: {
                                                top: 45,
                                                bottom: 0,
                                                left: 0,
                                            },
                                        },
                                    },
                                },
                            ],
                        };
                    },

                    get litecoinOptions() {
                        return {
                            series: [
                                {
                                    data: this.coins[this.coins.findIndex((d) => d.title === 'Litecoin')].series,
                                },
                            ],
                            chart: {
                                height: 45,
                                width: 120,
                                type: 'line',
                                sparkline: {
                                    enabled: true,
                                },
                            },
                            stroke: {
                                width: 2,
                            },
                            markers: {
                                size: 0,
                            },
                            colors: ['#e7515a'],
                            grid: {
                                padding: {
                                    top: 0,
                                    bottom: 0,
                                    left: 0,
                                },
                            },
                            tooltip: {
                                x: {
                                    show: false,
                                },
                                y: {
                                    title: {
                                        formatter: (formatter = () => {
                                            return '';
                                        }),
                                    },
                                },
                            },
                            responsive: [
                                {
                                    breakPoint: 576,
                                    options: {
                                        chart: {
                                            height: 95,
                                        },
                                        grid: {
                                            padding: {
                                                top: 45,
                                                bottom: 0,
                                                left: 0,
                                            },
                                        },
                                    },
                                },
                            ],
                        };
                    },

                    get binanceOptions() {
                        return {
                            series: [
                                {
                                    data: this.coins[this.coins.findIndex((d) => d.title === 'Binance')].series,
                                },
                            ],
                            chart: {
                                height: 45,
                                width: 120,
                                type: 'line',
                                sparkline: {
                                    enabled: true,
                                },
                            },
                            stroke: {
                                width: 2,
                            },
                            markers: {
                                size: 0,
                            },
                            colors: ['#00ab55'],
                            grid: {
                                padding: {
                                    top: 0,
                                    bottom: 0,
                                    left: 0,
                                },
                            },
                            tooltip: {
                                x: {
                                    show: false,
                                },
                                y: {
                                    title: {
                                        formatter: (formatter = () => {
                                            return '';
                                        }),
                                    },
                                },
                            },
                            responsive: [
                                {
                                    breakPoint: 576,
                                    options: {
                                        chart: {
                                            height: 95,
                                        },
                                        grid: {
                                            padding: {
                                                top: 45,
                                                bottom: 0,
                                                left: 0,
                                            },
                                        },
                                    },
                                },
                            ],
                        };
                    },

                    get solanaOptions() {
                        return {
                            series: [
                                {
                                    data: this.coins[this.coins.findIndex((d) => d.title === 'Solana')].series,
                                },
                            ],
                            chart: {
                                height: 45,
                                width: 120,
                                type: 'line',
                                sparkline: {
                                    enabled: true,
                                },
                            },
                            stroke: {
                                width: 2,
                            },
                            markers: {
                                size: 0,
                            },
                            colors: ['#e7515a'],
                            grid: {
                                padding: {
                                    top: 0,
                                    bottom: 0,
                                    left: 0,
                                },
                            },
                            tooltip: {
                                x: {
                                    show: false,
                                },
                                y: {
                                    title: {
                                        formatter: (formatter = () => {
                                            return '';
                                        }),
                                    },
                                },
                            },
                            responsive: [
                                {
                                    breakPoint: 576,
                                    options: {
                                        chart: {
                                            height: 95,
                                        },
                                        grid: {
                                            padding: {
                                                top: 45,
                                                bottom: 0,
                                                left: 0,
                                            },
                                        },
                                    },
                                },
                            ],
                        };
                    },

                    get tetherOptions() {
                        return {
                            series: [
                                {
                                    data: this.coins[this.coins.findIndex((d) => d.title === 'Tether')].series,
                                },
                            ],
                            chart: {
                                height: 45,
                                width: 120,
                                type: 'line',
                                sparkline: {
                                    enabled: true,
                                },
                            },
                            stroke: {
                                width: 2,
                            },
                            markers: {
                                size: 0,
                            },
                            colors: ['#00ab55'],
                            grid: {
                                padding: {
                                    top: 0,
                                    bottom: 0,
                                    left: 0,
                                },
                            },
                            tooltip: {
                                x: {
                                    show: false,
                                },
                                y: {
                                    title: {
                                        formatter: (formatter = () => {
                                            return '';
                                        }),
                                    },
                                },
                            },
                            responsive: [
                                {
                                    breakPoint: 576,
                                    options: {
                                        chart: {
                                            height: 95,
                                        },
                                        grid: {
                                            padding: {
                                                top: 45,
                                                bottom: 0,
                                                left: 0,
                                            },
                                        },
                                    },
                                },
                            ],
                        };
                    },

                    get selectedBitCoinOptions() {
                        return {
                            series: [
                                {
                                    data: this.selectedCoinObj.series,
                                },
                            ],
                            chart: {
                                height: 411,
                                type: 'line',
                                zoom: {
                                    enabled: false,
                                },
                                toolbar: {
                                    show: false,
                                },
                            },
                            stroke: {
                                width: 2,
                            },
                            markers: {
                                size: 0,
                            },
                            colors: ['#4361ee'],
                            grid: {
                                padding: {
                                    top: 0,
                                    bottom: 0,
                                    left: 0,
                                },
                            },
                            tooltip: {
                                x: {
                                    show: false,
                                },
                                y: {
                                    title: {
                                        formatter: (formatter = () => {
                                            return '';
                                        }),
                                    },
                                },
                            },
                            xaxis: {
                                type: 'datetime',
                                labels: {
                                    format: 'HH:mm',
                                },

                                axisBorder: {
                                    color: isDark ? '#191e3a' : '#e0e6ed',
                                },
                            },
                            yaxis: {
                                type: 'currency',
                                opposite: isRtl ? true : false,
                                labels: {
                                    offsetX: isRtl ? -40 : 0,
                                },
                            },
                            grid: {
                                borderColor: isDark ? '#191e3a' : '#e0e6ed',
                            },
                            responsive: [
                                {
                                    breakPoint: 576,
                                    options: {
                                        chart: {
                                            height: 95,
                                        },
                                        grid: {
                                            padding: {
                                                top: 45,
                                                bottom: 0,
                                                left: 0,
                                            },
                                        },
                                    },
                                },
                            ],
                        };
                    },

                    selectBitCoin(item) {
                        this.selectedCoinObj = item;
                        this.selectedBitCoin.updateSeries([
                            {
                                data: this.selectedCoinObj.series,
                            },
                        ]);
                        this.isShowCryptoMenu = false;
                    },
                }));
            });
        </script>
    @endsection
@endsection
