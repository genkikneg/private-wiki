<?php

namespace App\Http\Controllers;

use App\Models\BugReport;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BugTimelineController extends Controller
{
    public function index(): View
    {
        $bugReports = BugReport::latest()->get();
        
        return view('bug-timeline.index', compact('bugReports'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        BugReport::create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'open',
        ]);

        return redirect('/bug-timeline')
            ->with('success', 'バグレポートを投稿しました。');
    }

    public function update(Request $request, BugReport $bugReport): RedirectResponse
    {
        $validated = $this->validateWithBag('updateBugReport', $request, [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $bugReport->update($validated);

        return redirect('/bug-timeline')
            ->with('success', 'バグレポートを更新しました。');
    }

    public function updateStatus(Request $request, BugReport $bugReport): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:open,closed',
        ]);

        $bugReport->update([
            'status' => $request->status,
        ]);

        return redirect('/bug-timeline')
            ->with('success', 'ステータスを更新しました。');
    }

    public function destroy(BugReport $bugReport): RedirectResponse
    {
        $bugReport->delete();

        return redirect('/bug-timeline')
            ->with('success', 'バグレポートを削除しました。');
    }
}
