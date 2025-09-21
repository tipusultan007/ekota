<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\AccountTransferController;
use App\Http\Controllers\Admin\ExpenseCategoryController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\SalaryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\LoanAccountController;
use App\Http\Controllers\LoanInstallmentController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MyCollectionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SavingsAccountController;
use App\Http\Controllers\SavingsCollectionController;
use App\Http\Controllers\SavingsWithdrawalController;
use App\Http\Controllers\TranslationController;
use App\Http\Controllers\WorklistController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\CapitalInvestmentController;

// भविष्याে অন্যান্য কন্ট্রোলার এখানে যোগ করুন
// use App\Http\Controllers\MemberController;
// use App\Http\Controllers\CollectionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| এই ফাইলটি আপনার অ্যাপ্লিকেশনের ওয়েব রুটগুলোকে সংজ্ঞায়িত করে।
|
*/

// --- পাবলিক বা গেস্ট রুট (লগইন ছাড়াই অ্যাক্সেসযোগ্য) ---
// এই রুটগুলো লগইন সুরক্ষার বাইরে থাকবে।
Route::post('/language-switch', [LanguageController::class, 'switchLang'])->name('language.switch');
require __DIR__.'/auth.php'; // লগইন, রেজিস্ট্রেশন, পাসওয়ার্ড রিসেট ইত্যাদি রুট

// --- লগইন-সুরক্ষিত রুটসমূহ ---
// এই গ্রুপের ভেতরের সকল রুটের জন্য ব্যবহারকারীকে অবশ্যই লগইন করা থাকতে হবে।
Route::middleware(['auth', 'verified'])->group(function () {

    // মূল URL ('/') এখন ড্যাশবোর্ডে রিডাইরেক্ট করবে
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    // ড্যাশবোর্ড
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // প্রোফাইল রুট
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    // --- শুধুমাত্র অ্যাডমিনের জন্য নির্দিষ্ট রুট ---
    Route::middleware(['role:Admin'])->prefix('admin')->name('admin.')->group(function () {
        // এলাকা ব্যবস্থাপনা
        Route::resource('areas', AreaController::class);

        // भविष्याে অ্যাডমিনের অন্যান্য রুট এখানে যোগ হবে (যেমন: ব্যবহারকারী ব্যবস্থাপনা)
         Route::resource('users', UserController::class);

        // --- Expense Management Routes (নতুন যোগ করা হলো) ---
        Route::resource('expense-categories', ExpenseCategoryController::class);
        Route::resource('expenses', ExpenseController::class);

        Route::delete('/savings-withdrawals/{savingsWithdrawal}', [SavingsWithdrawalController::class, 'destroy'])->name('savings_withdrawals.destroy');
        Route::get('/reports/financial-summary', [ReportController::class, 'financialSummary'])->name('reports.financial_summary');
        Route::post('/loan-accounts/{loanAccount}/pay-off', [LoanAccountController::class, 'payOff'])->name('loan_accounts.pay_off');

        Route::resource('salaries', SalaryController::class);
        Route::resource('account-transfers', AccountTransferController::class);
        Route::resource('accounts', AccountController::class);


        Route::get('/translations', [TranslationController::class, 'index'])->name('translations.index');
        Route::post('/translations', [TranslationController::class, 'update'])->name('translations.update');
        Route::post('/translations/add', [TranslationController::class, 'store'])->name('translations.store');

        Route::get('/capital-investments', [CapitalInvestmentController::class, 'index'])->name('capital_investments.index');
        Route::post('/capital-investments', [CapitalInvestmentController::class, 'store'])->name('capital_investments.store');
        Route::get('/reports/area-wise', [ReportController::class, 'areaWiseReport'])->name('reports.area_wise');

    });


    // --- শুধুমাত্র মাঠকর্মীর জন্য নির্দিষ্ট রুট ---
    Route::middleware(['role:Field Worker'])->prefix('worker')->name('worker.')->group(function () {
        // भविष्याে মাঠকর্মীর জন্য নির্দিষ্ট কোনো পেজ লাগলে তার রুট এখানে যোগ হবে
        // Route::get('/my-collections', [CollectionController::class, 'myCollections'])->name('collections.my');
    });


    // --- অ্যাডমিন এবং মাঠকর্মী উভয়ের জন্য সাধারণ রুট ---
    // भविष्याে সদস্য, সঞ্চয়, ঋণ ইত্যাদি ব্যবস্থাপনার রুটগুলো এখানে যোগ করা যেতে পারে
     Route::middleware(['role:Admin|Field Worker'])->group(function () {
         Route::get('/members/create-with-account', [MemberController::class, 'createWithAccount'])->name('members.create_with_account');
         Route::post('/members/store-with-account', [MemberController::class, 'storeWithAccount'])->name('members.store_with_account');


         Route::resource('members', MemberController::class);
         Route::resource('members.savings-accounts', SavingsAccountController::class)->shallow();

         Route::get('new-savings',[SavingsAccountController::class,'newSavings'])->name('savings.new_savings');
         Route::post('new-savings',[SavingsAccountController::class,'newSavingsStore'])->name('savings.store');

         // সঞ্চয় আদায় ব্যবস্থাপনা (নতুন যোগ করা হলো)
         Route::resource('savings-collections', SavingsCollectionController::class);

         // ঋণ অ্যাকাউন্ট ব্যবস্থাপনা (নতুন যোগ করা হলো)
         Route::resource('members.loan-accounts', LoanAccountController::class)->shallow();

         Route::get('new-loan',[LoanAccountController::class,'newLoanAccount'])->name('loan.new');
         Route::post('new-loan',[LoanAccountController::class,'storeLoanAccount'])->name('loan.new.store');

         // ঋণ কিস্তি আদায় ব্যবস্থাপনা (নতুন যোগ করা হলো)
         Route::resource('loan-installments', LoanInstallmentController::class);

         Route::get('/daily-worklist', [WorklistController::class, 'today'])->name('worklist.today');

         Route::get('/reports/daily-collection', [ReportController::class, 'dailyCollectionForm'])->name('reports.daily_collection.form');
         Route::post('/reports/daily-collection', [ReportController::class, 'generateDailyCollectionReport'])->name('reports.daily_collection.generate');

         // Outstanding Loan Report (নতুন যোগ করা হলো)
         Route::get('/reports/outstanding-loan', [ReportController::class, 'outstandingLoanReport'])->name('reports.outstanding_loan');

         Route::post('/members/{member}/statement', [ReportController::class, 'generateMemberStatement'])->name('reports.member_statement');

         Route::post('/savings-accounts/{savingsAccount}/withdraw', [SavingsWithdrawalController::class, 'store'])->name('savings.withdraw');

         Route::get('/savings-withdrawals', [SavingsWithdrawalController::class, 'index'])->name('savings_withdrawals.index');
         Route::get('/savings-accounts/{savingsAccount}', [SavingsAccountController::class, 'show'])->name('savings_accounts.show');

         Route::get('/reports/daily-transaction-log', [ReportController::class, 'dailyTransactionLog'])->name('reports.daily_transaction_log');

         Route::get('/savings-accounts', [SavingsAccountController::class, 'index'])->name('savings_accounts.index');
         Route::get('/loan-accounts', [LoanAccountController::class, 'index'])->name('loan_accounts.index');
         Route::get('/loan-accounts/{loanAccount}', [LoanAccountController::class, 'show'])->name('loan_accounts.show');

         Route::get('/collections/create', [CollectionController::class, 'create'])->name('collections.create');
         Route::post('/collections/store', [CollectionController::class, 'store'])->name('collections.store');
// সদস্যের অ্যাকাউন্ট তথ্য আনার জন্য একটি API রুট
         Route::get('/api/members/{member}/accounts', [CollectionController::class, 'getMemberAccounts'])->name('api.member.accounts');

         Route::get('/my-collections', [MyCollectionController::class, 'index'])->name('my_collections.index');

     });

});
Route::middleware(['auth'])->prefix('api')->name('api.')->group(function () {
    // ... আপনার বিদ্যমান /members/{member}/accounts রুট ...
    Route::get('/savings-accounts/{savingsAccount}/details', [ApiController::class, 'getSavingsAccountDetails'])->name('savings_account.details');
    // routes/web.php -> api group
    Route::get('/loan-accounts/{loanAccount}/details', [ApiController::class, 'getLoanAccountDetails'])->name('loan_account.details');
    Route::get('/collections/savings', [MyCollectionController::class, 'getSavingsData'])->name('collections.savings.data');
    Route::get('/collections/loans', [MyCollectionController::class, 'getLoanData'])->name('collections.loans.data');

    Route::get('/collections/today-savings', [CollectionController::class, 'getTodaySavings'])->name('collections.today_savings');
    Route::get('/collections/today-loans', [CollectionController::class, 'getTodayLoans'])->name('collections.today_loans');
});
