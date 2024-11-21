<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePhotoRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\RestResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\AutoEncoder;
use Intervention\Image\ImageManager;

class AuthController extends Controller
{
    /**
     * Register a new user.
     *
     * @param  RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = User::create($request->all());
            return response()->json(new RestResource($user, 'Akun kamu berhasil dibuat.'), 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(new RestResource([], $th->getMessage(), false), 500);
        } finally {
            DB::commit();
        }
    }

    /**
     * Login user and return an access token.
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        // Mengambil hanya field 'username' dan 'password' dari request
        $input = $request->only('username', 'password');

        // Membuat validator untuk memeriksa validitas input
        $validator = Validator::make($input, [
            'username' => 'required', // Username harus diisi
            'password' => 'required', // Password harus diisi
        ]);

        // Memeriksa apakah validasi gagal
        if ($validator->fails()) {
            // Mengembalikan respons JSON dengan kesalahan validasi dan status 400
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 400);
        }

        // Memeriksa apakah terlalu banyak percobaan login dari IP ini
        if (RateLimiter::tooManyAttempts($request->ip(), 5)) {
            $timer = RateLimiter::availableIn($request->ip());
            $message = "Terlalu banyak percobaan login. Silahkan coba lagi dalam $timer detik.";
            return response()->json(new RestResource(null, $message, false), 429);
        }

        // Menentukan apakah 'username' adalah email atau nama pengguna
        $username = filter_var($input['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Membuat array kredensial untuk proses autentikasi
        $credentials = [$username => $input['username'], 'password' => $input['password']];

        // Mencoba untuk melakukan autentikasi dengan kredensial yang diberikan
        if (!auth()->attempt($credentials)) {
            RateLimiter::hit($request->ip());
            $message = "Akun kamu tidak ditemukan! Silahkan coba lagi.";
            return response()->json(new RestResource(null, $message, false), 403);
        }

        // Menghapus catatan percobaan login yang gagal untuk IP ini
        RateLimiter::clear($request->ip());

        // Mengambil data pengguna berdasarkan username atau email
        $user = User::where($username, $input['username'])->first();

        // Memperbarui data pengguna
        $user->update(['last_login' => now()]);

        // Menyusun data respons dengan email dan token akses
        $data = array_merge($user->toArray(), [
            'access_token' => $user->createToken('auth_token')->plainTextToken, // Membuat token akses
            'token_type' => 'Bearer', // Menentukan jenis token
        ]);

        // Mengembalikan respons JSON dengan data pengguna dan pesan sukses
        return response()->json(new RestResource($data, 'Kamu berhasil login ke dalam aplikasi.'), 200);
    }

    /**
     * Logout user (Revoke the token).
     *
     * @return void
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(new RestResource(null, 'Kamu berhasil logout.'), 200);
    }

    /**
     * Get the authenticated User.
     *
     * @return void
     */
    public function me(Request $request)
    {
        $user = $request->user()->load('posts');
        return response()->json(new RestResource($user, 'Data pengguna berhasil diambil.'), 200);
    }

    /**
     * changePhoto
     *
     * @param  mixed $request
     * @return void
     */
    public function changePhoto(ChangePhotoRequest $request)
    {
        $user = $request->user();

        // Jika request memiliki parameter 'delete'
        if (isset($request->delete)) {
            if (Storage::disk('public')->exists("img/users/{$user->photo}")) {
                Storage::disk('public')->delete("img/users/{$user->photo}");
            }

            // Mengubah foto pengguna menjadi foto default
            $user->update(['photo' => 'photo.png']);

            return response()->json(new RestResource($user, 'Foto kamu berhasil dihapus.'), 200);
        }

        // Mengambil file foto dari request
        $photo = $request->file('photo');

        // compress the image
        $manager = new ImageManager(Driver::class);
        $photoRead = $manager->read($photo);
        $photoEncode = $photoRead->encode(new AutoEncoder(quality: 20));

        // Menghapus foto lama jika ada
        if (Storage::disk('public')->exists("img/users/{$user->photo}")) {
            Storage::disk('public')->delete("img/users/{$user->photo}");
        }

        // Menyimpan foto baru ke dalam penyimpanan
        $fileName = uniqid('photo_') . '.' . $photo->getClientOriginalExtension();
        $photoEncode->save(storage_path("app/public/img/users/{$fileName}"));

        $user->update(['photo' => $fileName]);

        return response()->json(new RestResource($user, 'Foto kamu berhasil diubah.'), 200);
    }
}
