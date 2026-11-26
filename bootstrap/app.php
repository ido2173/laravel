<?php

use App\Http\Middleware\EnsureSiteIsWritable;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up'
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'writable' => EnsureSiteIsWritable::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (NotFoundHttpException $exception, Request $request): ?Response {
            if ($request->is('api/*') || $request->expectsJson()) {
                return null;
            }

            $modelNotFound = $exception->getPrevious();

            if (! ($modelNotFound instanceof ModelNotFoundException)) {
                return null;
            }

            if ($modelNotFound->getModel() === Post::class) {
                return response()->view('errors.article-introuvable', status: 404);
            }

            if ($modelNotFound->getModel() === Category::class) {
                $categories = Category::orderBy('name')->get();

                return response()->view('errors.category-introuvable', compact('categories'), status: 404);
            }

            return null;
        });
    })->create();
