<?php

namespace App\Filters;

use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AdminMenuFilter implements FilterInterface
{
    public function transform($item)
    {
        if (isset($item['text']) && $item['text'] === 'My Appointments') {
            $user = Auth::user();
            if ($user && $user instanceof \App\Models\User && $user->hasRole('admin')) {
                return false;
            }
        }

        if (isset($item['id']) && $item['id'] === 'make-appointment-menu-item') {
            $user = Auth::user();
            if (!$user || !($user instanceof \App\Models\User) || !$user->hasRole('subscriber')) {
                return false;
            }
        }

        return $item;
    }
}
