<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; 
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Friend;
use Illuminate\Support\Facades\DB;

class FriendController extends Controller
{

    // send friend request
    public function sendRequest(Request $request)
    {
        $request->validate([
            'receiverId' => 'required|integer|exists:users,id'
        ]);

        $senderId = auth()->user()->id;
        $receiverId = $request->receiverId;

        if ($senderId === $receiverId) {
            return response()->json(['success' => false, 'message' => 'You cannot friend yourself'], 400);
        }

        // Check if request already exists
        $exists = Friend::where('sender_id', $senderId)
            ->where('receiver_id', $receiverId)
            ->first();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Request already sent'], 400);
        }

        Friend::create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'status' => 'pending'
        ]);

        return response()->json(['success' => true, 'message' => 'Friend request sent'], 201);
    }

    // accept friend request
    public function acceptRequest(Request $request)
    {
        $request->validate([
            'requestId' => 'required|integer'
        ]);

        $receiverId = auth()->user()->id;

        $req = Friend::where('id', $request->requestId)
            ->where('receiver_id', $receiverId)
            ->where('status', 'pending')
            ->first();

        if (!$req) {
            return response()->json(['success' => false, 'message' => 'Invalid request'], 400);
        }

        $req->status = 'accepted';
        $req->save();

        return response()->json(['success' => true, 'message' => 'Friend request accepted']);
    }

    // reject friend request
    public function rejectRequest(Request $request)
    {
        $request->validate([
            'requestId' => 'required|integer'
        ]);

        $receiverId = auth()->user()->id;

        $req = Friend::where('id', $request->requestId)
            ->where('receiver_id', $receiverId)
            ->where('status', 'pending')
            ->first();

        if (!$req) {
            return response()->json(['success' => false, 'message' => 'Invalid request'], 400);
        }

        $req->status = 'rejected';
        $req->save();

        return response()->json(['success' => true, 'message' => 'Friend request rejected']);
    }

    // remove friend
    public function removeFriend(Request $request)
    {
        $request->validate([
            'friendId' => 'required|integer|exists:users,id'
        ]);

        $userId = auth()->user()->id;
        $friendId = $request->friendId;

        $friendship = Friend::where(function ($q) use ($userId, $friendId) {
            $q->where('sender_id', $userId)->where('receiver_id', $friendId);
        })->orWhere(function ($q) use ($userId, $friendId) {
            $q->where('sender_id', $friendId)->where('receiver_id', $userId);
        })->where('status', 'accepted')->first();

        if (!$friendship) {
            return response()->json(['success' => false, 'message' => 'You are not friends'], 400);
        }

        $friendship->delete();

        return response()->json(['success' => true, 'message' => 'Friend removed']);
    }

    // get pending requests
    public function pendingRequests()
    {
        $userId = auth()->user()->id;

        $requests = Friend::where('receiver_id', $userId)
            ->where('status', 'pending')
            ->with('sender:id,username')
            ->get()
            ->map(function ($request) {
            return [
                'requestId' => $request->id,      
                'sender' => [
                    'id' => $request->sender->id,
                    'username' => $request->sender->username,
                ],
                'status' => $request->status
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Pending requests retrieved',
            'data' => $requests
        ]);
    }

    // get friend list
    public function listFriends()
    {
        $userId = auth()->user()->id;

        $friends = Friend::where(function ($q) use ($userId) {
            $q->where('sender_id', $userId);
        })->orWhere(function ($q) use ($userId) {
            $q->where('receiver_id', $userId);
        })->where('status', 'accepted')
            ->with(['sender:id,username', 'receiver:id,username'])
            ->get();

        // clean list
        $clean = $friends->map(function ($row) use ($userId) {
            $friend = $row->sender_id == $userId ? $row->receiver : $row->sender;
            return [
                'id' => $friend->id,
                'username' => $friend->username
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'friends' => $clean,
                'count' => count($clean)
            ]
        ]);
    }
}

