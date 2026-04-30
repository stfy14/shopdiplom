<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CallbackRequest;
use Inertia\Inertia;

class CallbackController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Callbacks', [
            'callbacks' => CallbackRequest::latest()->get(),
        ]);
    }

    public function markProcessed(CallbackRequest $callback)
    {
        $callback->update(['status' => 'processed']);
        return back();
    }

    public function destroy(CallbackRequest $callback)
    {
        $callback->delete();
        return back();
    }
}