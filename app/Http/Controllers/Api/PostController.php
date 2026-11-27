<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PostIndexRequest;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\Api\PostResource;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    public function index(PostIndexRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();
        $posts = Post::published()
            ->when(isset($validated['category_id']), function (Builder $query) use ($validated): void {
                $query->where('category_id', $validated['category_id']);
            })
            ->when(isset($validated['tag_id']), function (Builder $query) use ($validated): void {
                $query->whereHas('tags', function (Builder $query) use ($validated): void {
                    $query->whereKey($validated['tag_id']);
                });
            })
            ->with($this->relationsForIncludes($validated['include'] ?? []))
            ->latest('published_at')
            ->latest('id')
            ->paginate($validated['per_page'] ?? 10)
            ->withQueryString();

        return PostResource::collection($posts);
    }

    public function show(Request $request, Post $post): PostResource
    {
        abort_unless($post->is_published, 404);

        $validated = $request->validate([
            'include' => ['sometimes', 'array'],
            'include.*' => ['string', Rule::in(['body', 'tags'])],
        ]);

        $post->load($this->relationsForIncludes($validated['include'] ?? []));

        return new PostResource($post);
    }

    public function store(StorePostRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['is_published'] = $request->boolean('is_published');
        $validated['published_at'] = $validated['is_published'] ? now() : null;

        $post = $request->user()->posts()->create(Arr::except($validated, ['tags']));
        $post->tags()->sync($validated['tags'] ?? []);
        $this->loadRelationsForResponse($post, $request);

        return (new PostResource($post))->response()->setStatusCode(201);
    }

    public function update(UpdatePostRequest $request, Post $post): PostResource
    {
        $validated = $request->validated();
        $validated['is_published'] = $request->boolean('is_published');
        $validated['published_at'] = $validated['is_published'] ? ($post->published_at ?? now()) : null;

        $post->update(Arr::except($validated, ['tags']));
        $post->tags()->sync($validated['tags'] ?? []);
        $this->loadRelationsForResponse($post, $request);

        return new PostResource($post);
    }

    public function destroy(Post $post): Response
    {
        Gate::authorize('delete', $post);

        $post->delete();

        return response()->noContent();
    }

    /**
     * @param  array<int, string>  $includes
     * @return array<int, string>
     */
    private function relationsForIncludes(array $includes): array
    {
        return in_array('tags', $includes, true)
            ? ['user', 'category', 'tags']
            : ['user', 'category'];
    }

    private function loadRelationsForResponse(Post $post, Request $request): void
    {
        $post->load($this->relationsForIncludes((array) $request->query('include', [])));
    }
}
