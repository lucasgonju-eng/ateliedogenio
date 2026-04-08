<?php

declare(strict_types=1);

namespace AtelieDoGenio\Http\Controller;

use AtelieDoGenio\Infrastructure\Supabase\SupabaseClient;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class UsersController extends BaseController
{
    public function __construct(private readonly SupabaseClient $client)
    {
    }

    public function names(ServerRequestInterface $request): ResponseInterface
    {
        $q = $this->query($request);
        $idsRaw = isset($q['ids']) && is_string($q['ids']) ? trim($q['ids']) : '';
        if ($idsRaw === '') {
            return $this->json(['users' => []]);
        }

        $ids = array_values(array_filter(array_map('trim', explode(',', $idsRaw)), static fn($v) => $v !== ''));
        if ($ids === []) {
            return $this->json(['users' => []]);
        }

        $in = '(' . implode(',', array_map(static fn($id) => $id, $ids)) . ')';

        try {
            $this->client->useServiceRole();
            $resp = $this->client->request('GET', 'rest/v1/users', [
                'query' => [
                    'select' => 'id,name,email',
                    'id' => 'in.' . $in,
                ],
            ]);
        } catch (\Throwable) {
            $resp = [];
        }

        $list = [];
        if (is_array($resp)) {
            $rows = $resp;
            if (!array_is_list($rows)) {
                $rows = [$rows];
            }
            foreach ($rows as $row) {
                if (!is_array($row)) { continue; }
                $list[] = [
                    'id' => (string)($row['id'] ?? ''),
                    'name' => is_string($row['name'] ?? null) ? $row['name'] : null,
                    'email' => is_string($row['email'] ?? null) ? $row['email'] : null,
                ];
            }
        }

        return $this->json(['users' => $list]);
    }
}

