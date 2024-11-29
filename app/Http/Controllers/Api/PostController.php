<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Http\Resources\RestResource;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\AutoEncoder;
use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\ImageManager;

class PostController extends Controller
{
    /**
     * For carousel in home page
     *
     * @return void
     */
    public function carousel()
    {
        $posts = Post::query()
            ->with(['user', 'category'])
            ->latest()
            ->take(4)
            ->get();

        return response()->json(new RestResource($posts, 'Postingan Berhasil Diambil!'), 200);
    }

    /**
     * Fetching all posts
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        // Ambil parameter page dan limit dari request, dengan nilai default
        $page = $request->query('page', 1);
        $limit = $request->query('limit', 8);
        $query = $request->query('q');

        // Hitung offset berdasarkan page dan limit
        $offset = ($page - 1) * $limit;

        // Ambil data dengan offset, limit, dan skip posting yang ada di carousel
        $posts = Post::query()
            ->with(['user', 'category'])
            ->latest();

        // Jika terdapat query pencarian
        if ($query) {
            $posts = $posts->where('title', 'like', "%{$query}%")
                ->skip($offset)
                ->take($limit);
        } else { // <-- buat dihalaman beranda
            $posts = $posts->skip(4 + $offset)  // Lewati 4 data pertama yang ditampilkan di carousel
                ->take($limit);
        }

        // Ambil data post
        $posts = $posts->get();

        // Jika data post kosong
        if ($posts->isEmpty()) {
            return response()->json(new RestResource([], 'Postingan Kosong!', false), 404);
        }

        // Total semua post untuk menentukan ada tidaknya halaman berikutnya
        $totalPosts = $query
            ? Post::query()->where('title', 'like', "%{$query}%")->count()
            : Post::count() - 4;

        // Cek apakah ada data berikutnya
        $hasMore = ($offset + $limit) < $totalPosts;

        // Response dengan data dan info load more
        return response()->json(
            [
                'status' => true,
                'message' => 'Postingan Berhasil Diambil!',
                'data' => $posts,
                'has_more' => $hasMore,
                'total' => $totalPosts,
            ],
            200,
        );
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
            return response()->json(new RestResource([], 'Postingan Tidak Ditemukan!', false), 404);
        }

        // Jika data post tidak kosong
        return response()->json(new RestResource($post, 'Postingan Berhasil Diambil!'), 200);
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

        // Jika request memiliki file thumbnail
        if ($request->hasFile('thumbnail'))
        {
            // get file thumbnail
            $thumbnail = $request->file('thumbnail');

            // for resize image
            $manager = new ImageManager(Driver::class);
            $thumbnailRead = $manager->read($thumbnail);
            $thumbnailEncode = $thumbnailRead->encode(new AutoEncoder(quality: 50));

            // create unique name for thumbnail and store in public/img/posts
            $fileName = uniqid('thumbnail_') . '.' . $thumbnail->getClientOriginalExtension();
            $thumbnailEncode->save(public_path('storage/img/posts/' . $fileName));

            // save thumbnail name to database
            $input['thumbnail'] = $fileName;
        }

        $input['slug'] = Str::slug($input['title']);
        $input['user_id'] = $request->user()->id;

        // Insert data post
        $post = Post::query()->create($input);

        // 201 Created response
        return response()->json(new RestResource($post, 'Postingan kamu berhasil dibuat!'), 201);
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
            return response()->json(new RestResource([], 'Postingan Tidak Ditemukan!', false), 404);
        }

        // Jika slug ada yang sama
        if (Post::query()->where('slug', Str::slug($input['title']))->where('id', '!=', $id)->exists()) {
            return response()->json(new RestResource([], 'Judul sudah ada, silahkan gunakan judul yang berbeda!', false), 400);
        }

        // Jika request tidak memiliki file thumbnail
        if (!$request->hasFile('thumbnail')) {
            $input['thumbnail'] = $post->thumbnail;
        }

        // Jika request memiliki file thumbnail
        if ($request->hasFile('thumbnail'))
        {
            // get file thumbnail
            $thumbnail = $request->file('thumbnail');

            // for resize image
            $manager = new ImageManager(Driver::class);
            $thumbnailRead = $manager->read($thumbnail);
            $thumbnailEncode = $thumbnailRead->encode(new AutoEncoder(quality: 20));

            // delete old thumbnail
            if (Storage::disk('public')->exists("img/posts/{$post->thumbnail}")) {
                Storage::disk('public')->delete("img/posts/{$post->thumbnail}");
            }

            // create unique name for thumbnail and store in public/img/posts
            $fileName = uniqid('thumbnail_') . '.' . $thumbnail->getClientOriginalExtension();
            $thumbnailEncode->save(public_path('storage/img/posts/' . $fileName));

            // save thumbnail name to database
            $input['thumbnail'] = $fileName;
        }

        $input['slug'] = Str::slug($input['title']);
        $input['category_id'] = (int) $input['category_id'];

        // Update data post
        $post->update($input);

        // 200 OK response
        return response()->json(new RestResource($post, 'Postingan kamu berhasil Diubah!'), 200);
    }

    /**
     * Delete a post by id
     *
     * @param  mixed  $id
     * @return JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        // Cari post berdasarkan id
        $post = Post::query()->find($id);

        // Jika data post kosong
        if (!$post) {
            return response()->json(new RestResource([], 'Postingan Tidak Ditemukan!', false), 404);
        }

        // Hapus thumbnail
        if (Storage::disk('public')->exists("img/posts/{$post->thumbnail}")) {
            Storage::disk('public')->delete("img/posts/{$post->thumbnail}");
        }

        // Hapus post
        $post->delete();

        // 200 OK response
        return response()->json(new RestResource([], 'Postingan kamu berhasil dihapus!'), 200);
    }

    /**
     * Fetching categories for select option
     *
     * @return void
     */
    public function fetchCategories()
    {
        $categories = Category::query()->select('id', 'name')->orderBy('name', 'asc')->get();

        // Jika data kategori kosong
        if ($categories->isEmpty()) {
            return response()->json(new RestResource([], 'Data Kategori Kosong!', false), 404);
        }

        // Jika data kategori tidak kosong
        return response()->json(new RestResource($categories, 'Data Kategori Berhasil Diambil!'), 200);
    }
}
