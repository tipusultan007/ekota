<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

// --- আপনার মডেল এবং অবজারভারগুলো ইম্পোর্ট করুন ---
use App\Models\Member;
use App\Observers\MemberObserver;
use App\Models\SavingsAccount;
use App\Observers\SavingsAccountObserver;
use App\Models\LoanAccount;
use App\Observers\LoanAccountObserver;
use App\Models\SavingsCollection;
use App\Observers\SavingsCollectionObserver;
use App\Models\LoanInstallment;
use App\Observers\LoanInstallmentObserver;
use App\Models\SavingsWithdrawal;
use App\Observers\SavingsWithdrawalObserver;
use App\Models\Guarantor;
use App\Observers\GuarantorObserver;
// ---------------------------------------------

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        //
    ];

    /**
     * The model observers for your application.
     *
     * @var array
     */
    protected $observers = [
        Member::class => [MemberObserver::class],
        SavingsAccount::class => [SavingsAccountObserver::class],
        LoanAccount::class => [LoanAccountObserver::class],
        SavingsCollection::class => [SavingsCollectionObserver::class],
        LoanInstallment::class => [LoanInstallmentObserver::class],
        SavingsWithdrawal::class => [SavingsWithdrawalObserver::class],
        Guarantor::class => [GuarantorObserver::class],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
