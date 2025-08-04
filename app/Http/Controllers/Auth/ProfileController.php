<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class ProfileController extends Controller
{
  public function show(Request $request)
  {
    /** @var User $user */
    $user = Auth::user();
    return response()->json(['user' => $user], 200);
  }

  public function update(Request $request)
  {
    /** @var User $user */
    $user = Auth::user();
    $rules = [
      'first_name' => ['required', 'string', 'max:255'],
      'last_name' => ['required', 'string', 'max:255'],
      'middle_name' => ['nullable', 'string', 'max:255'],
      'username' => ['required', 'string', 'max:255', 'unique:users,username,' . $user->id],
      'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
      'birth_date' => ['nullable', 'date'],
      'address' => ['required', 'string', 'max:255'],
      'city' => ['required', 'string', 'max:255'],
      'state' => ['required', 'string', 'max:255'],
      'country' => ['required', 'string', 'max:255'],
      'postal_code' => ['required', 'numeric', 'digits_between:1,20'],
      'phone' => ['nullable', 'string', 'max:20'],
      'profile_picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
    ];

    $data = Validator::make($request->all(), $rules)->validate();

    // Handle profile picture upload
    if ($request->hasFile('profile_picture')) {
      // Delete old profile picture if exists
      if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
        Storage::disk('public')->delete($user->profile_picture);
      }
      $data['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
    }

    $user->update($data);
    return response()->json(['message' => 'Profile updated successfully', 'user' => $user], 200);
  }

  public function changePassword(Request $request)
  {
    /** @var User $user */
    $user = Auth::user();
    $rules = [
      'current_password' => ['required', 'string'],
      'new_password' => ['required', 'string', 'min:8', 'confirmed'],
    ];
    $data = Validator::make($request->all(), $rules)->validate();
    if (!Hash::check($data['current_password'], $user->password)) {
      return response()->json(['message' => 'Current password is incorrect'], 403);
    }
    $user->password = bcrypt($data['new_password']);
    $user->save();
    return response()->json(['message' => 'Password changed successfully'], 200);
  }

  public function uploadProfilePicture(Request $request)
  {
    /** @var User $user */
    $user = Auth::user();
    $rules = [
      'profile_picture' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
    ];
    $data = Validator::make($request->all(), $rules)->validate();

    // Delete old profile picture if exists
    if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
      Storage::disk('public')->delete($user->profile_picture);
    }

    $path = $request->file('profile_picture')->store('profile_pictures', 'public');
    $user->profile_picture = $path;
    $user->save();
    return response()->json([
      'message' => 'Profile picture updated successfully',
      'profile_picture' => $path,
      'profile_picture_url' => Storage::url($path)
    ], 200);
  }
}
