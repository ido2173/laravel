<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PostIndexRequest;
use App\Http\Resources\Api\PostResource;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
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
}
