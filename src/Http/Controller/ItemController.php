<?php

namespace App\Http\Controller;

use App\Infrastructure\Supabase\SupabaseClient;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Http\Response\JsonResponse;

class ItemController
{
    private SupabaseClient $supabase;

    public function __construct(SupabaseClient $supabase)
    {
        $this->supabase = $supabase;
    }

    public function index(): ResponseInterface
    {
        $response = $this->supabase->from('items')->select('*');
        if ($response['error']) {
            return JsonResponse::error('500', $response['error']->getMessage(), 500);
        }
        return JsonResponse::success($response['data']);
    }

    public function show(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $id = $args['id'] ?? null;
        $response = $this->supabase->from('items')->select('*')->eq('id', $id)->single();
        if ($response['error']) {
            return JsonResponse::error('404', 'Item not found', 404);
        }
        return JsonResponse::success($response['data']);
    }

    public function store(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        if (empty($data['name'])) {
            return JsonResponse::error('400', 'Name is required', 400);
        }
        $response = $this->supabase->from('items')->insert($data)->single();
        if ($response['error']) {
            return JsonResponse::error('500', $response['error']->getMessage(), 500);
        }
        return JsonResponse::success($response['data'], 201);
    }

    public function update(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $id = $args['id'] ?? null;
        $data = $request->getParsedBody();
        if (empty($data['name'])) {
            return JsonResponse::error('400', 'Name is required', 400);
        }
        $response = $this->supabase->from('items')->update($data)->eq('id', $id)->single();
        if ($response['error']) {
            return JsonResponse::error('500', $response['error']->getMessage(), 500);
        }
        return JsonResponse::success($response['data']);
    }

    public function destroy(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $id = $args['id'] ?? null;
        $response = $this->supabase->from('items')->delete()->eq('id', $id);
        if ($response['error']) {
            return JsonResponse::error('500', $response['error']->getMessage(), 500);
        }
        return JsonResponse::success(null, 204);
    }
}
