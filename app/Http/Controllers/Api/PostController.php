<?php

namespace App\Http\Controllers\Api;

use JWTAuth;
use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Http\Requests\Api\CreatePostRequest;
use App\Http\Requests\Api\UpdatePostRequest;
use App\Http\Resources\Api\PostResource;
use App\Models\Post;
use App\Models\Tag;



class PostController extends Controller
{

    protected $user;

    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    public function index()
    {
        $posts = Post::with('tags')->latest()->paginate();
        return ApiResponse::success(PostResource::collection($posts));
    }

    public function user_posts()
    {
        $posts = $this->user->posts()->with('tags')->latest()->paginate();
        return ApiResponse::success(PostResource::collection($posts));
    }

    public function get_post($id)
    {
        $post = Post::find($id);
        if ($post) {
            return ApiResponse::success(new PostResource($post));
        }
        return ApiResponse::notFound();
    }



    // Store Method
    public function store(CreatePostRequest $request)
    {
        try {
            // Validation has passed, you can create the post and attach tags here
            // Extract the tags from the request
            $tagNames = $request->input('tags', []);

            // Create or retrieve existing tags
            $tagIds = [];
            foreach ($tagNames as $tagName) {
                $tag = Tag::firstOrCreate(['name' => $tagName]);
                $tagIds[] = $tag->id;
            }
            // Create the post and attach tags
            $post = $this->user->posts()->create($request->only(['title', 'body']));
            $post->tags()->sync($tagIds);

            return ApiResponse::success(new PostResource($post), 'Post successfully created', 201);
        } catch (\Exception $e) {
            return ApiResponse::serverError('Server Error!');
        }
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        try {
            if ($post) {

                if (auth()->id() !== $post->user_id) {
                    return ApiResponse::unauthorized('Unauthorized');
                }
                // Validate the request and update the post
                $post->update($request->only(['title', 'body']));

                // Extract the tags from the request
                $tagNames = $request->input('tags', []);

                // Create or retrieve existing tags
                $tagIds = [];
                foreach ($tagNames as $tagName) {
                    $tag = Tag::firstOrCreate(['name' => $tagName]);
                    $tagIds[] = $tag->id;
                }

                // Sync the tags for the updated post
                $post->tags()->sync($tagIds);

                return ApiResponse::success(new PostResource($post), 'Post successfully updated');
            }
            return ApiResponse::notFound();
        } catch (\Exception $e) {
            return ApiResponse::serverError('Server Error!');
        }
    }

    public function distroy(Post $post)
    {
        if (isset($post)) {
            if (auth()->id() !== $post->user_id) {
                return ApiResponse::unauthorized('Unauthorized');
            }
            $post->delete();
            return ApiResponse::success(null, 'Post successfully deleted');
        }
        return ApiResponse::notFound();
    }

    public function search()
    {
        $query = request()->input('query');

        $posts = Post::whereRaw("MATCH(title) AGAINST(? IN BOOLEAN MODE)", [$query])
            ->orWhereHas('tags', function ($q) use ($query) {
                $q->where('name', 'like', "%$query%");
            })
            ->paginate();

        return ApiResponse::success(PostResource::collection($posts), ($posts->count() ? 'Search results' : 'Not found'));
    }

    public function posts_by_tags()
    {
        $tagNames = request()->input('tags');
        $tagNamesArray = explode(',', $tagNames);
        $posts = Post::whereHas('tags', function ($query) use ($tagNamesArray) {
            $query->whereIn('name', $tagNamesArray);
        })->paginate();

        return ApiResponse::success(PostResource::collection($posts), ($posts->count() ? 'Posts by tags' : 'Not found'));
    }
}
