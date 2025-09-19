<?php

namespace App\Observers;

use App\Models\Guarantor;

class GuarantorObserver
{
    public function deleting(Guarantor $guarantor): void
    {
        $guarantor->clearMediaCollection('guarantor_nid');
        $guarantor->clearMediaCollection('guarantor_documents');
    }
}
