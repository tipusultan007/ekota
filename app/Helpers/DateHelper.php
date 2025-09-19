<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    /**
     * Calculates the next due date based on the commencement date, frequency, and last known due date.
     *
     * @param string|\Carbon\Carbon $commencementDate The start date of the account (opening/disbursement).
     * @param string $frequency 'daily', 'weekly', or 'monthly'.
     * @param string|\Carbon\Carbon|null $lastDueDate The last known next_due_date.
     * @return \Carbon\Carbon
     */
    public static function calculateNextDueDate($commencementDate, $frequency, $lastDueDate = null)
    {
        $today = Carbon::today();
        $commencement = Carbon::parse($commencementDate);
        $nextDueDate = $lastDueDate ? Carbon::parse($lastDueDate) : $commencement;


        if ($nextDueDate->isAfter($today)) {
            return $nextDueDate;
        }

        while ($nextDueDate->isBefore($today)) {
            switch ($frequency) {
                case 'daily':
                    $nextDueDate->addDay();
                    break;
                case 'weekly':
                    $nextDueDate->addWeek();
                    break;
                case 'monthly':
                    $nextDueDate->addMonthsNoOverflow();
                    break;
                default:
                    return $today->addDay();
            }
        }

        if ($frequency === 'daily') {
            return $today->addDay();
        }

        return $nextDueDate;
    }
}
