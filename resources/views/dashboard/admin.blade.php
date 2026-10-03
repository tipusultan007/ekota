@extends('layout.master')

@push('plugin-styles')
    <style>
        /* Premium Gradient Design System */
        .dashboard-hero {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            padding: 3rem 2rem;
            border-radius: 28px;
            color: white;
            position: relative;
            overflow: hidden;
            margin-bottom: 2.5rem;
            box-shadow: 0 20px 25px -5px rgba(79, 70, 229, 0.2);
        }

        .dashboard-hero::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            filter: blur(80px);
        }

        .stat-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            min-height: 140px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        /* Gradient Variants */
        .grad-blue { background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%); }
        .grad-emerald { background: linear-gradient(135deg, #059669 0%, #10b981 100%); }
        .grad-rose { background: linear-gradient(135deg, #e11d48 0%, #fb7185 100%); }
        .grad-violet { background: linear-gradient(135deg, #7c3aed 0%, #a78bfa 100%); }
        .grad-amber { background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%); }
        .grad-slate { background: linear-gradient(135deg, #334155 0%, #475569 100%); }

        .stat-icon {
            position: absolute;
            right: -20px;
            bottom: -20px;
            width: 120px;
            height: 120px;
            opacity: 0.15;
            color: white;
        }

        .card-label {
            font-size: 1.1rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 0.75rem;
            display: block;
        }

        .card-value {
            font-size: 2.5rem;
            font-weight: 900;
            color: white;
            margin-bottom: 0.5rem;
            line-height: 1.2;
        }

        .card-subtext {
            font-size: 0.85rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.75);
        }

        /* Widget Gradient Variants */
        .widget-grad-indigo { background: linear-gradient(135deg, #4338ca 0%, #6366f1 100%); }
        .widget-grad-teal { background: linear-gradient(135deg, #0d9488 0%, #14b8a6 100%); }
        .widget-grad-rose { background: linear-gradient(135deg, #be123c 0%, #f43f5e 100%); }
        .widget-grad-violet { background: linear-gradient(135deg, #6d28d9 0%, #8b5cf6 100%); }
        .widget-grad-sky { background: linear-gradient(135deg, #0369a1 0%, #0ea5e9 100%); }

        .widget-card {
            border: none;
            border-radius: 28px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            height: 100%;
            transition: all 0.3s ease;
        }

        .widget-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.2);
        }

        .widget-card .text-dark, 
        .widget-card .fw-bold { 
            color: white !important; 
        }

        .widget-card .text-muted {
            color: rgba(255, 255, 255, 0.7) !important;
        }

        .widget-header {
            padding: 2.25rem 2rem 1.25rem;
            background: rgba(255, 255, 255, 0.08);
            border-bottom: 1px solid rgba(255, 255, 255, 0.12) !important;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-radius: 28px 28px 0 0;
        }

        .widget-card .btn-light, 
        .widget-card .btn-white {
            background: rgba(255, 255, 255, 0.2) !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            color: white !important;
            backdrop-filter: blur(4px);
            transition: all 0.2s;
        }

        .widget-card .btn-light:hover, 
        .widget-card .btn-white:hover {
            background: rgba(255, 255, 255, 0.3) !important;
            transform: translateY(-2px);
        }

        .widget-card .badge {
            background: rgba(255, 255, 255, 0.15) !important;
            color: white !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
        }

        .widget-header h5, .widget-header h6 {
            font-size: 1.35rem;
            font-weight: 800;
            color: white !important;
            margin: 0;
        }

        .widget-body {
            padding: 1.5rem 2rem 2.5rem;
        }

        .transaction-item {
            display: flex;
            align-items: center;
            padding: 1.25rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            transition: all 0.2s;
        }

        .tx-info-title {
            font-size: 1rem;
            font-weight: 700;
            color: white;
        }

        .tx-info-date {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .tx-amount {
            font-size: 1.1rem;
            font-weight: 800;
            color: white;
        }

        .glass-box {
            background: rgba(255, 255, 255, 0.12) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            backdrop-filter: blur(4px);
        }

        .glass-icon-box {
            background: rgba(255, 255, 255, 0.25) !important;
            color: white !important;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
        }

        .tx-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.25rem;
            background: rgba(255, 255, 255, 0.2) !important;
            color: white !important;
        }

        .transaction-item:last-child { border-bottom: none; }

        .tx-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }

        /* Icon size classes */
        .icon-xs { width: 16px; height: 16px; }
        .icon-sm { width: 20px; height: 20px; }
        .icon-md { width: 24px; height: 24px; }
    </style>
@endpush

@section('content')
    {{-- Dashboard Hero --}}
    <div class="dashboard-hero">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="fw-extrabold mb-2 display-5 text-white">{{ __('messages.welcome_to_admin_dashboard') }}</h1>
                <p class="text-white-50 fs-5 mb-0">Here's what's happening in the society today.</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="bg-white bg-opacity-10 p-3 rounded-4 backdrop-blur d-inline-block border border-white border-opacity-20">
                    <div class="card-label mb-0 text-white-50">Last Login</div>
                    <div class="fw-bold text-white">{{ auth()->user()->last_login_at ? \Carbon\Carbon::parse(auth()->user()->last_login_at)->format('d M, H:i') : 'Just now' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Financial Summary Cards --}}
    <div class="row g-4 mb-5">
        <div class="col-xl-4 col-md-4 col-sm-6">
            <div class="card stat-card grad-blue">
                <div class="card-body p-4">
                    <div class="card-label">{{ __('messages.total_members') }}</div>
                    <div class="card-value">{{ number_format($totalMembers) }}</div>
                    <div class="card-subtext">{{ $activeMembers }} Active Members</div>
                    <i data-lucide="users" class="stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-4 col-sm-6">
            <div class="card stat-card grad-emerald">
                <div class="card-body p-4">
                    <div class="card-label">{{ __('messages.withdrawable_savings') }}</div>
                    <div class="card-value">{{ number_format($withdrawableAmount) }}</div>
                    <div class="card-subtext">Net Savings Balance</div>
                    <i data-lucide="piggy-bank" class="stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-4 col-sm-6">
            <div class="card stat-card grad-rose">
                <div class="card-body p-4">
                    <div class="card-label">{{ __('messages.total_withdrawn') }}</div>
                    <div class="card-value">{{ number_format($totalWithdrawn) }}</div>
                    <div class="card-subtext">All time withdrawals</div>
                    <i data-lucide="arrow-up-right" class="stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-4 col-sm-6">
            <div class="card stat-card grad-violet">
                <div class="card-body p-4">
                    <div class="card-label">{{ __('messages.loan_disbursed') }}</div>
                    <div class="card-value">{{ number_format($totalLoanDisbursed) }}</div>
                    <div class="card-subtext">Total Portfolio Value</div>
                    <i data-lucide="briefcase" class="stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-4 col-sm-6">
            <div class="card stat-card grad-amber">
                <div class="card-body p-4">
                    <div class="card-label">{{ __('messages.loan_on_field') }}</div>
                    <div class="card-value">{{ number_format($totalLoanDue) }}</div>
                    <div class="card-subtext">Principal + Interest Due</div>
                    <i data-lucide="hand-coins" class="stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-4 col-sm-6">
            @php $netPosition = $withdrawableAmount - $totalLoanDue; @endphp
            <div class="card stat-card grad-slate">
                <div class="card-body p-4">
                    <div class="card-label">{{ __('messages.net_position') }}</div>
                    <div class="card-value">{{ number_format($netPosition) }}</div>
                    <div class="card-subtext">Savings vs Portfolio Due</div>
                    <i data-lucide="activity" class="stat-icon"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        {{-- Charts Section --}}
        <div class="col-xl-8">
            <div class="row g-4">
                {{-- Monthly Growth Chart --}}
                <div class="col-12">
                    <div class="card widget-card widget-grad-indigo">
                        <div class="widget-header">
                            <h5 class="fw-bold text-dark m-0">{{ __('messages.monthly_collection_last_6_months') }}</h5>
                            <button class="btn btn-sm btn-light rounded-pill"><i data-lucide="download" class="icon-xs me-1"></i> Export</button>
                        </div>
                        <div class="widget-body">
                            <div id="monthlyCollectionChart"></div>
                        </div>
                    </div>
                </div>

                {{-- Area Wise & Statistics --}}
                <div class="col-md-6">
                    <div class="card widget-card widget-grad-teal">
                        <div class="widget-header">
                            <h6 class="fw-bold text-dark m-0">{{ __('messages.todays_statistics') }}</h6>
                            <span class="badge rounded-pill px-3">{{ \Carbon\Carbon::today()->format('d M') }}</span>
                        </div>
                        <div class="widget-body pt-4">
                            <div class="d-flex flex-column gap-3">
                                <div class="p-3 glass-box rounded-4 d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="p-2 glass-icon-box me-3"><i data-lucide="plus-circle" class="icon-sm"></i></div>
                                        <span class="text-white small fw-bold">{{ __('messages.savings_collection') }}</span>
                                    </div>
                                    <span class="fw-extrabold text-white">+{{ number_format($todaySavings) }}</span>
                                </div>
                                <div class="p-3 glass-box rounded-4 d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="p-2 glass-icon-box me-3"><i data-lucide="rotate-ccw" class="icon-sm"></i></div>
                                        <span class="text-white small fw-bold">{{ __('messages.loan_collection') }}</span>
                                    </div>
                                    <span class="fw-extrabold text-white">+{{ number_format($todayInstallments) }}</span>
                                </div>
                                <div class="p-3 glass-box rounded-4 d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="p-2 glass-icon-box me-3"><i data-lucide="minus-circle" class="icon-sm"></i></div>
                                        <span class="text-white small fw-bold">{{ __('messages.savings_withdrawal') }}</span>
                                    </div>
                                    <span class="fw-extrabold text-white">-{{ number_format($todayWithdrawals) }}</span>
                                </div>
                                <div class="p-3 glass-box rounded-4 d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="p-2 glass-icon-box me-3"><i data-lucide="banknote" class="icon-sm"></i></div>
                                        <span class="text-white small fw-bold">{{ __('messages.other_expenses') }}</span>
                                    </div>
                                    <span class="fw-extrabold text-white">-{{ number_format($todayExpenses) }}</span>
                                </div>
                                @php $netCashFlow = ($todaySavings + $todayInstallments) - ($todayWithdrawals + $todayExpenses); @endphp
                                <div class="p-3 glass-box rounded-4 d-flex justify-content-between align-items-center border border-white border-opacity-30" style="background: rgba(255,255,255,0.2) !important;">
                                    <span class="fw-bold text-white fs-5">Net Cash Flow</span>
                                    <span class="fw-extrabold fs-4 text-white">{{ number_format($netCashFlow) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card widget-card widget-grad-rose">
                        <div class="widget-header">
                            <h6 class="fw-bold text-dark m-0">{{ __('messages.members_by_area') }}</h6>
                        </div>
                        <div class="widget-body">
                            <div id="areaPieChart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar Widgets --}}
        <div class="col-xl-4">
            <div class="row g-4">
                {{-- Account Balances --}}
                <div class="col-12">
                    <div class="card widget-card widget-grad-violet">
                        <div class="widget-header border-bottom">
                            <h6 class="fw-bold text-dark m-0">Payment Accounts</h6>
                            <i data-lucide="landmark" class="icon-sm text-white-50"></i>
                        </div>
                        <div class="widget-body pt-4">
                            @foreach($paymentAccounts as $account)
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-white bg-opacity-10 p-2 rounded-3 me-3">
                                            <i data-lucide="{{ str_contains(strtolower($account->name), 'bank') ? 'building-2' : 'wallet' }}" class="icon-sm text-white"></i>
                                        </div>
                                        <div>
                                            <div class="tx-info-title">{{ $account->name }}</div>
                                            <div class="tx-info-date text-white-50">{{ $account->code }}</div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="tx-amount">{{ number_format($account->balance) }}</div>
                                        <span class="badge bg-white bg-opacity-20 text-white" style="font-size: 0.7rem;">Active</span>
                                    </div>
                                </div>
                            @endforeach
                            <a href="{{ route('admin.accounts.index') }}" class="btn btn-white w-100 rounded-pill py-3 mt-2 fw-bold">Manage All Accounts</a>
                        </div>
                    </div>
                </div>

                {{-- Recent Transactions --}}
                <div class="col-12">
                    <div class="card widget-card widget-grad-sky">
                        <div class="widget-header border-bottom">
                            <h6 class="fw-bold text-dark m-0">Recent Transactions</h6>
                            <i data-lucide="clock" class="icon-sm text-white-50"></i>
                        </div>
                        <div class="widget-body pt-1">
                            @forelse($recentTransactions as $tx)
                                <div class="transaction-item">
                                    <div class="tx-icon-box">
                                        <i data-lucide="activity" class="icon-sm"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="tx-info-title">{{ Str::limit($tx->description, 35) }}</div>
                                        <div class="tx-info-date">{{ $tx->date->format('d M, Y') }}</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="tx-amount">{{ number_format($tx->journalEntries->where('account.is_payment_account', true)->sum('debit') ?: $tx->journalEntries->where('account.is_payment_account', true)->sum('credit')) }}</div>
                                        <div class="text-white-50 fw-bold" style="font-size: 0.7rem;">{{ class_basename($tx->transactionable_type) }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5 text-white-50 fw-bold">No recent transactions found.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('plugin-scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        $(document).ready(function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Modernizing ApexCharts Colors
            const colors = ['#4f46e5', '#10b981', '#f43f5e', '#f59e0b', '#06b6d4'];

            // Monthly Collection Bar Chart
            var optionsBar = {
                chart: { 
                    type: 'area', 
                    height: 350,
                    toolbar: { show: false },
                    zoom: { enabled: false }
                },
                stroke: { curve: 'smooth', width: 4 },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.7,
                        opacityTo: 0.2,
                        stops: [0, 100]
                    }
                },
                colors: ['#ffffff', 'rgba(255,255,255,0.6)'],
                series: [
                    { name: "{{ __('messages.savings') }}", data: @json($monthlyCollections['savings']) },
                    { name: "{{ __('messages.loans') }}", data: @json($monthlyCollections['loans']) }
                ],
                xaxis: { 
                    categories: @json($monthlyCollections['months']),
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { style: { colors: '#fff' } }
                },
                yaxis: {
                    labels: { style: { colors: '#fff' } }
                },
                grid: {
                    borderColor: 'rgba(255,255,255,0.1)',
                    strokeDashArray: 4,
                    padding: { left: 0, right: 0 }
                },
                legend: { 
                    position: 'top', 
                    horizontalAlign: 'right',
                    labels: { colors: '#fff' }
                }
            };
            var chartBar = new ApexCharts(document.querySelector("#monthlyCollectionChart"), optionsBar);
            chartBar.render();

            // Area wise Members Pie Chart
            var optionsPie = {
                chart: { type: 'donut', height: 280 },
                colors: ['#fff', 'rgba(255,255,255,0.8)', 'rgba(255,255,255,0.6)', 'rgba(255,255,255,0.4)'],
                plotOptions: {
                    pie: {
                        donut: {
                            size: '75%',
                            labels: {
                                show: true,
                                name: { color: '#fff' },
                                value: { color: '#fff' },
                                total: {
                                    show: true,
                                    label: 'Total Members',
                                    color: '#fff',
                                    formatter: () => '{{ $totalMembers }}'
                                }
                            }
                        }
                    }
                },
                series: @json($areaWiseMembers->pluck('count')),
                labels: @json($areaWiseMembers->pluck('name')),
                legend: { 
                    position: 'bottom',
                    labels: { colors: '#fff' }
                },
                stroke: { width: 0 }
            };
            var chartPie = new ApexCharts(document.querySelector("#areaPieChart"), optionsPie);
            chartPie.render();

            // Trigger window resize to fix ApexCharts rendering labels
            setTimeout(function() {
                window.dispatchEvent(new Event('resize'));
            }, 500);
        });
    </script>
@endpush
