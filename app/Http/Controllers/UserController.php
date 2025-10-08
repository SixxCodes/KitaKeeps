<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Employee;
use App\Models\Branch;
use App\Models\UserBranch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'username' => 'required|string|max:255|unique:user,username,' . $user->user_id . ',user_id',
            'user_image' => 'nullable|image|max:2048',
        ]);

        $user->username = $request->username;

        if ($request->hasFile('user_image')) {
            // Store new image first
            $folder = in_array(strtolower($user->role), ['owner', 'admin', 'cashier'])
                ? 'employees'
                : 'users';

            $filename = 'user_' . $user->user_id . '_' . time() . '.' . $request->file('user_image')->extension();
            $path = $request->file('user_image')->storeAs($folder, $filename, 'public');

            // Sync to employee first
            if (in_array(strtolower($user->role), ['owner', 'admin', 'cashier']) && $user->UserbelongsToperson && $user->UserbelongsToperson->employee) {
                $employee = $user->UserbelongsToperson->employee;

                // Delete old image if exists
                if ($employee->employee_image_path && Storage::disk('public')->exists($employee->employee_image_path)) {
                    Storage::disk('public')->delete($employee->employee_image_path);
                }

                // Update with new image path
                $employee->update(['employee_image_path' => $path]);
            }

            // Delete old user image after employee updated
            if ($user->user_image_path && Storage::disk('public')->exists($user->user_image_path)) {
                Storage::disk('public')->delete($user->user_image_path);
            }

            // Save new path to user
            $user->user_image_path = $path;
        }

        $user->save();

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }

}
