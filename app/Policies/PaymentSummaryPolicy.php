<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PaymentSummary;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentSummaryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_payment::summary');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PaymentSummary $paymentSummary): bool
    {
        return $user->can('view_payment::summary');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_payment::summary');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PaymentSummary $paymentSummary): bool
    {
        return $user->can('update_payment::summary');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PaymentSummary $paymentSummary): bool
    {
        return $user->can('delete_payment::summary');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_payment::summary');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, PaymentSummary $paymentSummary): bool
    {
        return $user->can('force_delete_payment::summary');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_payment::summary');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, PaymentSummary $paymentSummary): bool
    {
        return $user->can('restore_payment::summary');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_payment::summary');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, PaymentSummary $paymentSummary): bool
    {
        return $user->can('replicate_payment::summary');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_payment::summary');
    }
}
