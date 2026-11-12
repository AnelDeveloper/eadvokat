<?php

namespace App\Http\Controllers;

use App\Actions\RegisterOffice;
use App\Http\Requests\RegisterOfficeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    public function store(RegisterOfficeRequest $request, RegisterOffice $register): RedirectResponse
    {
        $user = $register->handle($request->payload());

        Auth::login($user);
        $request->session()->regenerate();

        Inertia::flash('toast', __('messages.registered', ['office' => $user->office->name]));

        return to_route('matters.create');
    }
}
