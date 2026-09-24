<?php

namespace App\Http\Controllers;

use App\Application\User\ManageUser;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', ['user' => auth()->user()]);
    }

    public function updatePassword(Request $request, ManageUser $useCase)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8), 'different:current_password'],
        ]);
        $useCase->changeOwnPassword($this->actorId(), $data['password']);

        return back()->with('success', 'Kata sandi berhasil diganti.');
    }
}
