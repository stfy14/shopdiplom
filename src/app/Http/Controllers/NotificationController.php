<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }

    public function destroyAll(Request $request)
    {
        $request->user()->notifications()->delete();
        return response()->json(['success' => true]);
    }

    public function destroy($id, Request $request)
    {
        $request->user()->notifications()->where('id', $id)->delete();
        return response()->json(['success' => true]);
    }
}