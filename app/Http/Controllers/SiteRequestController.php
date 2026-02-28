<?php

namespace App\Http\Controllers;

use App\Models\SiteRequest;
use Illuminate\Http\Request;

class SiteRequestController extends Controller
{
    public function index(Request $request)
    {
        $requests = SiteRequest::with('site')
            ->when($request->domain, fn($q) => $q->where('domain', $request->domain))
            ->when($request->success !== null, fn($q) => $q->where('success', $request->success))
            ->latest()
            ->paginate(30);

        return view('site-requests.index', compact('requests'));
    }

}
