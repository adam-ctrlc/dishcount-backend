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
      'address' => ['nullable', 'string', 'max:255'],
      'city' => ['nullable', 'string', 'max:255'],
      'state' => ['nullable', 'string', 'max:255'],
      'country' => ['nullable', 'string', 'max:255'],
      'postal_code' => ['nullable', 'numeric', 'digits_between:1,20'],
      'phone' => ['nullable', 'string', 'max:20'],
      'profile_picture' => ['nullable', 'string'], 
    ];

    $validator = Validator::make($request->all(), $rules);
    
    if ($validator->fails()) {
      return response()->json([
        'message' => 'Validation failed',
        'errors' => $validator->errors()
      ], 422);
    }
    
    $data = $validator->validated();

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
      'profile_picture' => ['required', 'string'],
    ];
    $data = Validator::make($request->all(), $rules)->validate();

    $user->profile_picture = $data['profile_picture'];
    $user->save();
    return response()->json([
      'message' => 'Profile picture updated successfully',
      'profile_picture' => $data['profile_picture']
    ], 200);
  }
}
