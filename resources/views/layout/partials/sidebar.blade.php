<nav class="sidebar">
    <div class="sidebar-header">
        <a href="{{ route('dashboard') }}" class="sidebar-brand">
            সমিতি<span>সফট</span>
        </a>
        <div class="sidebar-toggler not-active">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
    <div class="sidebar-body">
        <ul class="nav" id="sidebarNav">

            {{-- Main Navigation --}}
            <li class="nav-item nav-category">{{ __('messages.main') }}</li>
            <li class="nav-item {{ active_class(['dashboard']) }}">
                <a href="{{ route('dashboard') }}" class="nav-link">
                    <i class="link-icon" data-lucide="home"></i>
                    <span class="link-title">{{ __('messages.dashboard') }}</span>
                </a>
            </li>

            <li class="nav-item {{ active_class(['daily-worklist']) }}">
                <a href="{{ route('worklist.today') }}" class="nav-link">
                    <i class="link-icon" data-lucide="clipboard-list"></i>
                    <span class="link-title">{{ __('messages.todays_worklist') }}</span>
                </a>
            </li>


            {{-- Daily Operations --}}
            <li class="nav-item nav-category">{{ __('messages.operations') }}</li>
            <li class="nav-item {{ active_class(['collections/create']) }}">
                <a href="{{ route('collections.create') }}" class="nav-link">
                    <i class="link-icon" data-lucide="dollar-sign"></i>
                    <span class="link-title">{{ __('messages.collections') }}</span>
                </a>
            </li>

            <li class="nav-item {{ active_class(['loan-installments/create']) }}">
                <a href="{{ route('loan-installments.create') }}" class="nav-link">
                    <i class="link-icon" data-lucide="trending-up"></i>
                    <span class="link-title">{{ __('messages.loan_collection') }}</span>
                </a>
            </li>


            {{-- Accounts & Members --}}
            <li class="nav-item nav-category">{{ __('messages.accounts') }}</li>
            <li class="nav-item {{ active_class(['members*']) }}">
                <a href="{{ route('members.index') }}" class="nav-link">
                    <i class="link-icon" data-lucide="users"></i>
                    <span class="link-title">{{ __('messages.member_management') }}</span>
                </a>
            </li>
            <li class="nav-item {{ active_class(['savings-accounts*']) }}">
                <a href="{{ route('savings_accounts.index') }}" class="nav-link">
                    <i class="link-icon" data-lucide="wallet"></i>
                    <span class="link-title">{{ __('messages.savings_accounts') }}</span>
                </a>
            </li>
            <li class="nav-item {{ active_class(['loan-accounts*']) }}">
                <a href="{{ route('loan_accounts.index') }}" class="nav-link">
                    <i class="link-icon" data-lucide="hand-coins"></i>
                    <span class="link-title">{{ __('messages.loan_accounts') }}</span>
                </a>
            </li>

            <li class="nav-item {{ active_class(['savings-collections', 'loan-installments', 'savings-withdrawals*']) }}">
                <a class="nav-link" data-bs-toggle="collapse" href="#history" role="button"
                    aria-expanded="{{ is_active_route(['savings-collections', 'loan-installments', 'savings-withdrawals*']) }}"
                    aria-controls="history">
                    <i class="link-icon" data-lucide="archive"></i>
                    <span class="link-title">{{ __('messages.collection_report') }}</span>
                    <i class="link-arrow" data-lucide="chevron-down"></i>
                </a>
                <div
                    class="collapse {{ show_class(['savings-collections', 'loan-installments', 'savings-withdrawals*']) }}"
                    id="history">
                    <ul class="nav sub-menu">
                        <li class="nav-item">
                            <a href="{{ route('savings-collections.index') }}"
                                class="nav-link {{ active_class(['savings-collections']) }}">{{ __('messages.collection_history') }}</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('loan-installments.index') }}"
                                class="nav-link {{ active_class(['loan-installments']) }}">{{ __('messages.installment_history') }}</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('savings_withdrawals.index') }}"
                                class="nav-link {{ active_class(['savings-withdrawals*']) }}">{{ __('messages.withdrawal_history') }}</a>
                        </li>
                    </ul>
                </div>
            </li>


            {{-- Reports (Admin Only) --}}
            @role('Admin')
            <li class="nav-item nav-category">{{ __('messages.reports') }}</li>
            <li class="nav-item {{ active_class(['reports/*']) }}">
                <a class="nav-link" data-bs-toggle="collapse" href="#reports" role="button"
                    aria-expanded="{{ is_active_route(['reports/*']) }}" aria-controls="reports">
                    <i class="link-icon" data-lucide="bar-chart-2"></i>
                    <span class="link-title">{{ __('messages.reports') }}</span>
                    <i class="link-arrow" data-lucide="chevron-down"></i>
                </a>
                <div class="collapse {{ show_class(['reports/*']) }}" id="reports">
                    <ul class="nav sub-menu">
                        <li class="nav-item"><a href="{{ route('reports.daily_collection.form') }}"
                                class="nav-link {{ active_class(['reports/daily-collection']) }}">{{ __('messages.daily_collection_report') }}</a>
                        </li>
                        <li class="nav-item"><a href="{{ route('reports.daily_transaction_log') }}"
                                class="nav-link {{ active_class(['reports/daily-transaction-log']) }}">{{ __('messages.daily_transaction_log') }}</a>
                        </li>
                        <li class="nav-item"><a href="{{ route('reports.outstanding_loan') }}"
                                class="nav-link {{ active_class(['reports/outstanding-loan']) }}">{{ __('messages.outstanding_loans') }}</a>
                        </li>
                        <li class="nav-item"><a href="{{ route('admin.reports.financial_summary') }}"
                                class="nav-link {{ active_class(['admin/reports/financial-summary']) }}">{{ __('messages.financial_summary') }}</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.reports.area_wise') }}" class="nav-link {{ active_class(['admin/reports/area-wise']) }}">{{ __('messages.area_wise_report') }}</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.reports.journal_ledger') }}" class="nav-link {{ active_class(['admin/reports/journal-ledger']) }}">Journal Ledger</a>
                        </li>
                    </ul>
                </div>
            </li>
            @endrole


            {{-- Management (Admin Only) --}}
            @role('Admin')
            <li class="nav-item nav-category">{{ __('messages.management') }}</li>
            <li class="nav-item {{ active_class(['admin/areas*']) }}">
                <a href="{{ route('admin.areas.index') }}" class="nav-link">
                    <i class="link-icon" data-lucide="map-pin"></i>
                    <span class="link-title">{{ __('messages.area_management') }}</span>
                </a>
            </li>
            <li class="nav-item {{ active_class(['admin/users*']) }}">
                <a href="{{ route('admin.users.index') }}" class="nav-link">
                    <i class="link-icon" data-lucide="user-cog"></i>
                    <span class="link-title">{{ __('messages.user_management') }}</span>
                </a>
            </li>
            <li class="nav-item {{ active_class(['admin/capital-investments*']) }}">
                <a href="{{ route('admin.capital_investments.index') }}" class="nav-link">
                    <i class="link-icon" data-lucide="trending-up"></i>
                    <span class="link-title">{{ __('messages.capital_investment') }}</span>
                </a>
            </li>
            <li class="nav-item {{ active_class(['admin/expense*']) }}">
                <a class="nav-link" data-bs-toggle="collapse" href="#expense" role="button"
                    aria-expanded="{{ is_active_route(['admin/expense*']) }}" aria-controls="expense">
                    <i class="link-icon" data-lucide="credit-card"></i>
                    <span class="link-title">{{ __('messages.expense_management') }}</span>
                    <i class="link-arrow" data-lucide="chevron-down"></i>
                </a>
                <div class="collapse {{ show_class(['admin/expense*']) }}" id="expense">
                    <ul class="nav sub-menu">
                        <li class="nav-item"><a href="{{ route('admin.expense-categories.index') }}"
                                class="nav-link {{ active_class(['admin/expense-categories*']) }}">{{ __('messages.expense_categories') }}</a>
                        </li>
                        <li class="nav-item"><a href="{{ route('admin.expenses.index') }}"
                                class="nav-link {{ active_class(['admin/expenses*']) }}">{{ __('messages.all_expenses') }}</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item {{ active_class(['admin/salaries*']) }}">
                <a class="nav-link" data-bs-toggle="collapse" href="#salary" ...>
                    <i class="link-icon" data-lucide="wallet"></i>
                    <span class="link-title">{{ __('messages.salary_management') }}</span>
                </a>
                <div class="collapse {{ show_class(['admin/salaries*']) }}" id="salary">
                    <ul class="nav sub-menu">
                        <li class="nav-item">
                            <a href="{{ route('admin.salaries.create') }}" class="nav-link {{ active_class(['admin/salaries/create']) }}">
                                {{ __('messages.pay_salary') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.salaries.index') }}" class="nav-link {{ active_class(['admin/salaries']) }}">
                                {{ __('messages.payment_history') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endrole
            @role('Admin')
            <li class="nav-item {{ active_class(['admin/accounts*', 'admin/account-transfers*']) }}">
                <a class="nav-link" data-bs-toggle="collapse" href="#chartOfAccounts">
                    <i class="link-icon" data-lucide="book"></i>
                    <span class="link-title">{{ __('messages.chart_of_accounts') }}</span>
                    <i class="link-arrow" data-lucide="chevron-down"></i>
                </a>
                <div class="collapse {{ show_class(['admin/accounts*', 'admin/account-transfers*']) }}" id="chartOfAccounts">
                    <ul class="nav sub-menu">
                        <li class="nav-item">
                            <a href="{{ route('admin.accounts.index') }}" class="nav-link {{ active_class(['admin/accounts*']) }}">
                                {{ __('messages.all_accounts') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.account-transfers.index') }}" class="nav-link {{ active_class(['admin/account-transfers*']) }}">
                                {{ __('messages.balance_transfer') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endrole
        </ul>
    </div>
</nav>