<?php

declare(strict_types=1);

namespace App\Http\Controllers\Audit;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class AuditController extends Controller
{
    public function index(Request $request): View
    {
        $query = Activity::with(['causer', 'subject'])
            ->orderByDesc('created_at');

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('causer_id')) {
            $query->where('causer_type', User::class)
                  ->where('causer_id', $request->causer_id);
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', 'like', '%'.$request->subject_type.'%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn ($w) => $w
                ->where('description', 'ilike', "%{$q}%")
                ->orWhere('log_name', 'ilike', "%{$q}%")
            );
        }

        $activities = $query->paginate(30)->withQueryString();
        $users      = User::orderBy('name')->get(['id', 'name', 'email']);
        $events     = ['created', 'updated', 'deleted', 'restored'];

        return view('pages.audit.index', compact('activities', 'users', 'events'));
    }
}
