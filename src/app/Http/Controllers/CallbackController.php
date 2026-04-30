<?php

namespace App\Http\Controllers;

use App\Models\CallbackRequest;
use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Http\Request;

class CallbackController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:100',
            'phone'   => ['required', 'string', 'regex:/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/'],
            'comment' => 'nullable|string|max:500',
        ], [
            'name.required'    => 'Введите ваше имя',
            'phone.required'   => 'Введите номер телефона',
            'phone.regex'      => 'Введите телефон в формате +7 (999) 000-00-00',
        ]);

        CallbackRequest::create($request->only('name', 'phone', 'comment'));

        User::where('role', 'admin')->get()->each(function ($admin) use ($request) {
            $admin->notify(new AppNotification(
                "Новый запрос на звонок от {$request->name} ({$request->phone})",
                'success',
                '/admin/callbacks',
                'order'
            ));
        });

        return back()->with('callback_sent', true);
    }
}