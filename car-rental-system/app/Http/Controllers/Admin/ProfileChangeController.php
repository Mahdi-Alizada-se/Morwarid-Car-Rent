<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProfileChangeRequest;
use App\Services\ProfileChangeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ProfileChangeController extends Controller
{
    public function __construct(
        private ProfileChangeService $profileChangeService,
    ) {
    }

    // ─── Index ────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = ProfileChangeRequest::with('user')->latest();

        $status = $request->get('status', 'pending');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $requests = $query->paginate(15)->withQueryString();
        $pendingCount = ProfileChangeRequest::where('status', 'pending')->count();

        return view('admin.profile-requests.index', compact('requests', 'pendingCount', 'status'));
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    public function show(ProfileChangeRequest $profileChangeRequest): View
    {
        $profileChangeRequest->load(['user', 'reviewer']);

        return view('admin.profile-requests.show', [
            'profileChangeRequest' => $profileChangeRequest,
        ]);
    }

    // ─── Approve ──────────────────────────────────────────────────────────────

    public function approve(ProfileChangeRequest $profileChangeRequest): RedirectResponse
    {
        if (!$profileChangeRequest->isPending()) {
            return back()->with('error', 'This request has already been reviewed.');
        }

        $this->profileChangeService->approve($profileChangeRequest, auth()->user());

        Cache::flush();

        return redirect()
            ->route('admin.profile-requests.index')
            ->with('success', 'Profile changes approved and applied.');
    }

    // ─── Reject ───────────────────────────────────────────────────────────────

    public function reject(Request $request, ProfileChangeRequest $profileChangeRequest): RedirectResponse
    {
        if (!$profileChangeRequest->isPending()) {
            return back()->with('error', 'This request has already been reviewed.');
        }

        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $this->profileChangeService->reject($profileChangeRequest, auth()->user(), $request->reason);

        Cache::flush();

        return redirect()
            ->route('admin.profile-requests.index')
            ->with('success', 'Profile change request rejected.');
    }
}