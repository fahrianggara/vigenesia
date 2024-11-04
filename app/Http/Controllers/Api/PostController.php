<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Http\Resources\RestResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Fetching all posts
     *
     * @return JsonResponse
     */
    public function index()
    {
        $posts = Post::query()->with(['user', 'category'])->latest()->paginate(10);

        // Jika data post kosong
        if ($posts->isEmpty()) {
            return response()->json(new RestResource([], 'Data Postingan Kosong!', false), 404);
        }

        // Jika data post tidak kosong
        return response()->json(new RestResource($posts, 'Data Postingan Berhasil Diambil!'), 200);
    }

    /**
     * Showing a post by id
     *
     * @param  mixed $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $post = Post::query()->with(['user', 'category'])->find($id);

        // Jika data post kosong
        if (!$post) {
            return response()->json(new RestResource([], 'Data Postingan Tidak Ditemukan!', false), 404);
        }

        // Jika data post tidak kosong
        return response()->json(new RestResource($post, 'Data Postingan Berhasil Diambil!'), 200);
    }

    /**
     * Store a new post
     *
     * @param  PostRequest  $request
     * @return JsonResponse
     */
    public function store(PostRequest $request)
    {
        $input = $request->validated();

        // jika slug ada yang sama
        if (Post::query()->where('slug', Str::slug($input['title']))->exists()) {
            return response()->json(new RestResource([], 'Judul sudah ada, silahkan gunakan judul yang berbeda!', false), 400);
        }

        $input['slug'] = Str::slug($input['title']);
        $input['user_id'] = $request->user()->id;

        // Insert data post
        $post = Post::query()->create($input);

        // 201 Created response
        return response()->json(new RestResource($post, 'Data Postingan Berhasil Ditambahkan!'), 201);
    }

    /**
     * Update a post by id
     *
     * @param  PostRequest  $request
     * @param  mixed  $id
     * @return JsonResponse
     */
    public function update(PostRequest $request, $id)
    {
        $input = $request->validated();

        // Cari post berdasarkan id
        $post = Post::query()->find($id);

        // Jika data post kosong
        if (!$post) {
            return response()->json(new RestResource([], 'Data Postingan Tidak Ditemukan!', false), 404);
        }

        // Jika user yang mengedit bukan pemilik post
        if ($request->user()->id !== $post->user_id) {
            return response()->json(new RestResource([], 'Anda tidak memiliki akses untuk mengedit postingan ini!', false), 403);
        }

        // Jika slug ada yang sama
        if (Post::query()->where('slug', Str::slug($input['title']))->where('id', '!=', $id)->exists()) {
            return response()->json(new RestResource([], 'Judul sudah ada, silahkan gunakan judul yang berbeda!', false), 400);
        }

        $input['slug'] = Str::slug($input['title']);
        $input['category_id'] = (int) $input['category_id'];

        // Update data post
        $post->update($input);

        // 200 OK response
        return response()->json(new RestResource($post, 'Data Postingan Berhasil Diubah!'), 200);
    }

    /**
     * Delete a post by id
     *
     * @param  mixed  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        // Cari post berdasarkan id
        $post = Post::query()->find($id);

        // Jika data post kosong
        if (!$post) {
            return response()->json(new RestResource([], 'Data Postingan Tidak Ditemukan!', false), 404);
        }

        // Hapus post
        $post->delete();

        // 200 OK response
        return response()->json(new RestResource([], 'Data Postingan Berhasil Dihapus!'), 200);
    }
}
