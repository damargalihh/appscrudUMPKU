<?php

namespace App\Http\Controllers;

use App\Models\LogActivity;
use Illuminate\Http\Request;

class LogActivityController extends Controller
{
    public function index(Request $request)
    {
        // Hanya full admin yang bisa akses, pastikan middleware diterapkan di route
        $query = LogActivity::with('user')->orderByDesc('created_at');

        // Ambil daftar username unik untuk filter dropdown
        $usernames = LogActivity::select('username')->distinct()->orderBy('username')->pluck('username');

        // Optional: filter by action, username, status, date, dsb
        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }
        if ($request->filled('username')) {
            $query->where('username', $request->username);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->get();
        return view('admin.log_activities.index', compact('logs', 'usernames'));
    }
}
