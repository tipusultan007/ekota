<nav class="sidebar">
    <div class="sidebar-header">
        <a href="{{ route('dashboard') }}" class="sidebar-brand">
            পদ্মা <span>সমবায়</span>
        </a>
        <div class="sidebar-toggler not-active"><span></span><span></span><span></span></div>
    </div>
    <div class="sidebar-body">
        <ul class="nav" id="sidebarNav">

            {{-- ======================================================= --}}
            {{-- ============== MAIN & COMMON LINKS ============== --}}
            {{-- ======================================================= --}}
            <li class="nav-item nav-category">{{ __('messages.main') }}</li>
            
            <li class="nav-item {{ active_class(['dashboard']) }}">
                <a href="{{ route('dashboard') }}" class="nav-link">
                    <i class="link-icon" data-lucide="home"></i>
                    <span class="link-title">{{ __('messages.dashboard') }}</span>
                </a>
            </li>
            
            {{-- Daily Operations --}}
            <li class="nav-item {{ active_class(['daily-worklist']) }}">
                <a href="{{ route('worklist.today') }}" class="nav-link">
                    <i class="link-icon" data-lucide="calendar"></i>
                    <span class="link-title">{{ __('messages.todays_worklist') }}</span>
                </a>
            </li>
            <li class="nav-item {{ active_class(['collections/create']) }}">
                <a href="{{ route('collections.create') }}" class="nav-link">
                    <i class="link-icon" data-lucide="plus-circle"></i>
                    <span class="link-title">{{ __('messages.collection_entry') }}</span>
                </a>
            </li>
            
            <li class="nav-item {{ active_class(['my-collections']) }}">
                <a href="{{ route('my_collections.index') }}" class="nav-link">
                    <i class="link-icon" data-lucide="history"></i>
                    <span class="link-title">{{ __('messages.my_collection_history') }}</span>
                </a>
            </li>

            <li class="nav-item {{ active_class(['reports/irregular-loan-payments']) }}">
                <a href="{{ route('reports.irregular_loan_payments') }}" class="nav-link">
                    <i class="link-icon" data-lucide="alert-circle"></i>
                    <span class="link-title">{{ __('messages.irregular_loan_payments') }}</span>
                </a>
            </li>

            {{-- Member & Account Lists --}}
            <li class="nav-item nav-category">{{ __('messages.members_and_accounts') }}</li>

            <li class="nav-item {{ active_class(['members*']) }}">
                <a href="{{ route('members.index') }}" class="nav-link">
                    <i class="link-icon" data-lucide="users"></i>
                    <span class="link-title">{{ __('messages.member_management') }}</span>
                </a>
            </li>
            
            <li class="nav-item {{ active_class(['savings-accounts*']) }}">
                <a href="{{ route('savings_accounts.index') }}" class="nav-link">
                    <i class="link-icon" data-lucide="piggy-bank"></i>
                    <span class="link-title">{{ __('messages.savings_accounts') }}</span>
                </a>
            </li>

            <li class="nav-item {{ active_class(['loan-accounts*']) }}">
                <a href="{{ route('loan_accounts.index') }}" class="nav-link">
                    <i class="link-icon" data-lucide="banknote"></i>
                    <span class="link-title">{{ __('messages.loan_accounts') }}</span>
                </a>
            </li>

            {{-- ======================================================= --}}
            {{-- ============== ADMIN ONLY LINKS ============== --}}
            {{-- ======================================================= --}}
            @role('Admin')
            
            {{-- Finance & Accounting --}}
            <li class="nav-item nav-category">{{ __('messages.accounting') }}</li>

            <li class="nav-item {{ active_class(['admin/accounts*', 'admin/account-transfers*', 'admin/income*', 'admin/expense*', 'admin/salaries*']) }}">
                <a class="nav-link" data-bs-toggle="collapse" href="#finance" role="button" aria-expanded="{{ is_active_route(['admin/accounts*', 'admin/account-transfers*', 'admin/income*', 'admin/expense*', 'admin/salaries*']) }}" aria-controls="finance">
                    <i class="link-icon" data-lucide="calculator"></i>
                    <span class="link-title">{{ __('messages.accounting') }}</span>
                    <i class="link-arrow" data-lucide="chevron-down"></i>
                </a>
                <div class="collapse {{ show_class(['admin/accounts*', 'admin/account-transfers*', 'admin/income*', 'admin/expense*', 'admin/salaries*']) }}" id="finance">
                    <ul class="nav sub-menu">
                        <li class="nav-item"><a href="{{ route('admin.accounts.index') }}" class="nav-link {{ active_class(['admin/accounts*']) }}">{{ __('messages.all_accounts') }}</a></li>
                        <li class="nav-item"><a href="{{ route('admin.account-transfers.index') }}" class="nav-link {{ active_class(['admin/account-transfers*']) }}">{{ __('messages.balance_transfer') }}</a></li>
                        <li class="nav-item"><a href="{{ route('admin.expenses.index') }}" class="nav-link {{ active_class(['admin/expenses*']) }}">{{ __('messages.all_expenses') }}</a></li>
                        <li class="nav-item"><a href="{{ route('admin.incomes.index') }}" class="nav-link {{ active_class(['admin/incomes*']) }}">{{ __('messages.all_incomes') }}</a></li>
                        <li class="nav-item"><a href="{{ route('admin.salaries.index') }}" class="nav-link {{ active_class(['admin/salaries*']) }}">{{ __('messages.salary_management') }}</a></li>
                    </ul>
                </div>
            </li>

            {{-- Reports & History --}}
            <li class="nav-item nav-category">{{ __('messages.reports') }}</li>

            <li class="nav-item {{ active_class(['reports/*', 'admin/reports*']) }}">
                <a class="nav-link" data-bs-toggle="collapse" href="#reports" role="button" aria-expanded="{{ is_active_route(['reports/*', 'admin/reports*']) }}" aria-controls="reports">
                    <i class="link-icon" data-lucide="bar-chart-2"></i>
                    <span class="link-title">{{ __('messages.reports') }}</span>
                    <i class="link-arrow" data-lucide="chevron-down"></i>
                </a>
                <div class="collapse {{ show_class(['reports/*', 'admin/reports*']) }}" id="reports">
                    <ul class="nav sub-menu">
                        <li class="nav-item"><a href="{{ route('admin.reports.financial_summary') }}" class="nav-link {{ active_class(['admin/reports/financial-summary']) }}">{{ __('messages.financial_summary') }}</a></li>
                        <li class="nav-item"><a href="{{ route('admin.reports.daily_cashbook') }}" class="nav-link {{ active_class(['admin/reports/daily-cashbook']) }}">{{ __('messages.daily_cashbook') }}</a></li>
                        {{-- <li class="nav-item"><a href="{{ route('admin.reports.journal_ledger') }}" class="nav-link {{ active_class(['admin/reports/journal-ledger']) }}">{{ __('messages.journal_ledger') }}</a></li> --}}
                        <li class="nav-item"><a href="{{ route('reports.outstanding_loan') }}" class="nav-link {{ active_class(['reports/outstanding-loan']) }}">{{ __('messages.outstanding_loans') }}</a></li>
                        <li class="nav-item"><a href="{{ route('admin.reports.area_wise') }}" class="nav-link {{ active_class(['admin/reports/area-wise']) }}">{{ __('messages.area_wise_report') }}</a></li>
                        <li class="nav-item"><a href="{{ route('admin.reports.field_officer_wise') }}" class="nav-link {{ active_class(['admin/reports/field-officer-wise']) }}">{{ __('messages.field_officer_wise_report') }}</a></li>
                    </ul>
                </div>
            </li>

            <li class="nav-item {{ active_class(['savings-collections*', 'loan-installments*', 'savings-withdrawals*']) }}">
                 <a class="nav-link" data-bs-toggle="collapse" href="#history" role="button" aria-expanded="{{ is_active_route(['savings-collections*', 'loan-installments*', 'savings-withdrawals*']) }}" aria-controls="history">
                    <i class="link-icon" data-lucide="archive"></i>
                    <span class="link-title">{{ __('messages.transaction_histories') }}</span>
                    <i class="link-arrow" data-lucide="chevron-down"></i>
                </a>
                <div class="collapse {{ show_class(['savings-collections*', 'loan-installments*', 'savings-withdrawals*']) }}" id="history">
                    <ul class="nav sub-menu">
                        <li class="nav-item"><a href="{{ route('savings-collections.index') }}" class="nav-link {{ active_class(['savings-collections*']) }}">{{ __('messages.collection_history') }}</a></li>
                        <li class="nav-item"><a href="{{ route('loan-installments.index') }}" class="nav-link {{ active_class(['loan-installments*']) }}">{{ __('messages.installment_history') }}</a></li>
                    </ul>
                </div>
            </li>
            
            {{-- System Management --}}
            <li class="nav-item nav-category">{{ __('messages.system') }}</li>
            
            <li class="nav-item {{ active_class(['admin/areas*', 'admin/users*', 'admin/translations*', 'admin/income-categories*', 'admin/expense-categories*']) }}">
                <a class="nav-link" data-bs-toggle="collapse" href="#system" role="button" aria-expanded="{{ is_active_route(['admin/areas*', 'admin/users*', 'admin/translations*', 'admin/income-categories*', 'admin/expense-categories*']) }}" aria-controls="system">
                    <i class="link-icon" data-lucide="settings"></i>
                    <span class="link-title">{{ __('messages.system') }}</span>
                    <i class="link-arrow" data-lucide="chevron-down"></i>
                </a>
                <div class="collapse {{ show_class(['admin/areas*', 'admin/users*', 'admin/translations*', 'admin/income-categories*', 'admin/expense-categories*']) }}" id="system">
                    <ul class="nav sub-menu">
                        <li class="nav-item"><a href="{{ route('admin.areas.index') }}" class="nav-link {{ active_class(['admin/areas*']) }}">{{ __('messages.area_management') }}</a></li>
                        <li class="nav-item"><a href="{{ route('admin.users.index') }}" class="nav-link {{ active_class(['admin/users*']) }}">{{ __('messages.user_management') }}</a></li>
                        <li class="nav-item"><a href="{{ route('admin.income-categories.index') }}" class="nav-link {{ active_class(['admin/income-categories*']) }}">{{ __('messages.income_categories') }}</a></li>
                        <li class="nav-item"><a href="{{ route('admin.expense-categories.index') }}" class="nav-link {{ active_class(['admin/expense-categories*']) }}">{{ __('messages.expense_categories') }}</a></li>
                        <li class="nav-item"><a href="{{ route('admin.translations.index') }}" class="nav-link {{ active_class(['admin/translations*']) }}">{{ __('messages.translations') }}</a></li>
                        <li class="nav-item">
                            <a href="{{ route('admin.activity_logs.index') }}" class="nav-link {{ active_class(['activity-logs*']) }}">{{ __('messages.activity_logs') }}</a>
                        </li>
                    </ul>
                </div>
            </li>
            @endrole

        </ul>
    </div>
</nav>