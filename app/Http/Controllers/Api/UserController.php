<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RestResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show($id)
    {
        $user = User::query()->withCount('posts')->with('posts')->find($id);

        // Jika data user tidak ditemukan
        if (!$user) {
            return response()->json(new RestResource(null, 'Data user tidak ditemukan!', false), 404);
        }

        // Jika data user ditemukan
        return response()->json(new RestResource($user, 'Data user berhasil diambil', true), 200);
    }
}
