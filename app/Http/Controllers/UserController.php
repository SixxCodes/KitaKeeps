<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function updateProfile(Request $request)
    {
        $user = Auth::user(); // Get the currently logged-in user

        // Validate input
        $request->validate([
            'username' => 'required|string|max:255',
            'user_image' => 'nullable|image|max:2048',
        ]);

        // Update username
        $user->username = $request->username;

        // Update profile image if uploaded
        if ($request->hasFile('user_image')) {
            // Store image in 'storage/app/public/users' folder
            $path = $request->file('user_image')->store('users', 'public');
            $user->user_image_path = 'storage/' . $path; // Save path to DB
        }

        $user->save(); // Save changes
    }
}
