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

        return response()->json(new RestResource($posts, 'Data Postingan Berhasil Diambil!'), 200);
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

        // Hitung offset berdasarkan page dan limit
        $offset = ($page - 1) * $limit;

        // Ambil data dengan offset, limit, dan skip posting yang ada di carousel
        $posts = Post::query()
            ->with(['user', 'category'])
            ->latest()
            ->skip(4 + $offset)  // Lewati 4 data pertama yang ditampilkan di carousel
            ->take($limit)
            ->get();

        // Jika data post kosong
        if ($posts->isEmpty()) {
            return response()->json(new RestResource([], 'Data Postingan Kosong!', false), 404);
        }

        // Total semua post untuk menentukan ada tidaknya halaman berikutnya
        $totalPosts = Post::count() - 4;  // Kurangi 4 dari total untuk carousel
        $hasMore = ($offset + $limit) < $totalPosts;

        // Gabungkan data post dengan info load more
        $datas = array_merge($posts->toArray(), [
            'current_page' => $page,
            'has_more' => $hasMore,
        ]);

        // Response dengan data dan info load more
        return response()->json(new RestResource($datas, 'Data Postingan Berhasil Diambil!'), 200);
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
        return response()->json(new RestResource($post, 'Data Postingan Berhasil Diubah!'), 200);
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
            return response()->json(new RestResource([], 'Data Postingan Tidak Ditemukan!', false), 404);
        }

        // Jika user yang menghapus bukan pemilik post
        if ($request->user()->id !== $post->user_id) {
            return response()->json(new RestResource([], 'Anda tidak memiliki akses untuk menghapus postingan ini!', false), 403);
        }

        // Hapus thumbnail
        if (Storage::disk('public')->exists("img/posts/{$post->thumbnail}")) {
            Storage::disk('public')->delete("img/posts/{$post->thumbnail}");
        }

        // Hapus post
        $post->delete();

        // 200 OK response
        return response()->json(new RestResource([], 'Data Postingan Berhasil Dihapus!'), 200);
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
