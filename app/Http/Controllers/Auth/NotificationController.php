<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class NotificationController extends Controller
{
  /**
   * List all notifications for the authenticated user.
   */
  public function index()
  {
    $user = Auth::user();
    $notifications = Notification::where('user_id', $user->id)
      ->orderBy('created_at', 'desc')
      ->get();

    return response()->json(['notifications' => $notifications], 200);
  }

  /**
   * Show a single notification.
   */
  public function show($id)
  {
    $user = Auth::user();
    $notification = Notification::where('user_id', $user->id)
      ->findOrFail($id);

    return response()->json(['notification' => $notification], 200);
  }

  /**
   * Mark a notification as read.
   */
  public function update(Request $request, $id)
  {
    $user = Auth::user();
    $notification = Notification::where('user_id', $user->id)
      ->findOrFail($id);

    $notification->read_at = now();
    $notification->save();

    return response()->json(['message' => 'Notification marked as read'], 200);
  }

  /**
   * Delete a notification.
   */
  public function destroy($id)
  {
    $user = Auth::user();
    $notification = Notification::where('user_id', $user->id)
      ->findOrFail($id);

    $notification->delete();

    return response()->json(['message' => 'Notification deleted'], 200);
  }
}
