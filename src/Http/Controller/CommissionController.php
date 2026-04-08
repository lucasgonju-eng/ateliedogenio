<?php

declare(strict_types=1);

namespace AtelieDoGenio\Http\Controller;

use AtelieDoGenio\Domain\Entity\Sale;
use AtelieDoGenio\Domain\Enum\SaleStatus;
use AtelieDoGenio\Domain\Repository\SaleRepositoryInterface;
use AtelieDoGenio\Infrastructure\Email\SymfonyMailer;
use AtelieDoGenio\Infrastructure\Supabase\SupabaseClient;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

final class CommissionController extends BaseController
{
    private const COMMISSION_RATE = 0.02;
    private const BASE_SALARY = 1844.00;
    private const RECEIPT_DIR = '/financeiro/comissoes';
    private const RECEIPT_WEB_PATH = '/financeiro/comissoes';

    public function __construct(
        private readonly SaleRepositoryInterface $sales,
        private readonly SupabaseClient $supabase,
        private readonly SymfonyMailer $mailer
    ) {
    }

    public function vendorSummary(ServerRequestInterface $request): ResponseInterface
    {
        $user = $this->user($request);

        $sales = $this->sales->search([
            'user_id' => $user['id'],
            'with_items' => true,
        ]);

        $today = (new \DateTimeImmutable('today'))->format('Y-m-d');
        $month = (new \DateTimeImmutable('first day of this month'))->format('Y-m');

        $rate = self::COMMISSION_RATE; // 2% sobre o valor bruto (subtotal)
        $baseMonthly = self::BASE_SALARY;
        $todayTotal = 0.0;
        $monthCommissionTotal = 0.0;
        $lifetimeTotal = 0.0;

        $items = [];

        /** @var list<Sale> $completed */
        $completed = array_values(array_filter($sales, function (Sale $sale): bool {
            return in_array($sale->status(), [SaleStatus::PAGA, SaleStatus::ENTREGUE], true);
        }));

        // Ordena do mais recente para o mais antigo
        usort($completed, function (Sale $a, Sale $b): int {
            return $b->createdAt() <=> $a->createdAt();
        });

        foreach ($completed as $sale) {
            $createdDate = $sale->createdAt()->format('Y-m-d');
            $createdMonth = $sale->createdAt()->format('Y-m');

            $commissionForSale = $sale->subtotal()->toFloat() * $rate;
            $lifetimeTotal += $commissionForSale;

            if ($createdDate === $today) {
                $todayTotal += $commissionForSale;
            }

            if ($createdMonth === $month) {
                $monthCommissionTotal += $commissionForSale;
            }

            foreach ($sale->items() as $item) {
                $lineTotal = $item->unitPrice()->toFloat() * $item->quantity();
                $commission = $lineTotal * $rate;

                $label = $item->productName() ?: ($item->productSku() ? ('SKU ' . $item->productSku()) : 'Produto');

                $items[] = [
                    'sale_id' => $sale->id(),
                    'sale_short' => substr($sale->id(), -5),
                    'created_at' => $sale->createdAt()->format(DATE_ATOM),
                    'product' => $label,
                    'size' => $item->size(),
                    'qty' => $item->quantity(),
                    'unit_price' => $item->unitPrice()->toFloat(),
                    'line_total' => $lineTotal,
                    'commission' => $commission,
                ];
            }
        }

        // limita os itens para exibicao
        $items = array_slice($items, 0, 100);

        return $this->json([
            'rate' => $rate,
            'base_salary' => $baseMonthly,
            'today_total' => $todayTotal,
            'month_total' => $monthCommissionTotal,
            'month_total_with_base' => $baseMonthly + $monthCommissionTotal,
            'lifetime_total' => $lifetimeTotal,
            'items' => $items,
        ]);
    }

    public function adminVendors(ServerRequestInterface $request): ResponseInterface
    {
        $rows = [];

        try {
            $response = $this->supabase->runWithServiceRole(function () {
                return $this->supabase->request('GET', 'rest/v1/users', [
                    'query' => [
                        'select' => 'id,name,email,roles(name)',
                        'order' => 'name.asc',
                        'roles.name' => 'eq.vendedor',
                    ],
                ]);
            });

            if (is_array($response)) {
                $rows = array_is_list($response) ? $response : [$response];
            }
        } catch (\Throwable) {
            $rows = [];
        }

        if ($rows === []) {
            try {
                $response = $this->supabase->runWithServiceRole(function () {
                    return $this->supabase->request('GET', 'rest/v1/users', [
                        'query' => [
                            'select' => 'id,name,email,roles(name)',
                            'order' => 'name.asc',
                        ],
                    ]);
                });
                if (is_array($response)) {
                    $rows = array_is_list($response) ? $response : [$response];
                }
            } catch (\Throwable) {
                $rows = [];
            }
        }

        $vendors = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $roleName = $this->extractRoleName($row['roles'] ?? null) ?? ($row['role'] ?? null);
            if ($roleName !== null && !in_array($roleName, ['vendedor', 'vendor'], true)) {
                continue;
            }

            $id = (string) ($row['id'] ?? '');
            if ($id === '') {
                continue;
            }

            $vendors[] = [
                'id' => $id,
                'name' => is_string($row['name'] ?? null) ? $row['name'] : null,
                'email' => is_string($row['email'] ?? null) ? $row['email'] : null,
            ];
        }

        return $this->json(['vendors' => $vendors]);
    }

    public function adminSummary(ServerRequestInterface $request): ResponseInterface
    {
        $query = $this->query($request);
        $vendorId = isset($query['vendor_id']) ? trim((string) $query['vendor_id']) : '';
        if ($vendorId === '') {
            return $this->error('VALIDATION_ERROR', 'Vendor_id e obrigatorio.', 422);
        }

        $monthParam = isset($query['month']) ? trim((string) $query['month']) : '';
        $periodStart = $this->resolveMonthStart($monthParam);
        $periodEnd = $periodStart->modify('last day of this month');
        $today = new \DateTimeImmutable('today');

        $rate = self::COMMISSION_RATE;
        $baseMonthly = self::BASE_SALARY;
        $todayTotal = 0.0;
        $monthTotal = 0.0;

        $sales = $this->sales->search([
            'user_id' => $vendorId,
            'from' => $periodStart->setTime(0, 0, 0)->format(DATE_ATOM),
            'to' => $periodEnd->setTime(23, 59, 59)->format(DATE_ATOM),
        ]);

        foreach ($sales as $sale) {
            if (!in_array($sale->status(), [SaleStatus::PAGA, SaleStatus::ENTREGUE], true)) {
                continue;
            }

            $commissionForSale = $sale->subtotal()->toFloat() * $rate;
            $createdDate = $sale->createdAt()->format('Y-m-d');
            $createdMonth = $sale->createdAt()->format('Y-m');

            if ($createdMonth === $periodStart->format('Y-m')) {
                $monthTotal += $commissionForSale;
            }

            if ($createdDate === $today->format('Y-m-d')) {
                $todayTotal += $commissionForSale;
            }
        }

        $closing = $this->fetchClosing($vendorId, $periodStart);
        $closingDate = $closing['closing_date'] ?? $today->format('Y-m-d');

        return $this->json([
            'vendor_id' => $vendorId,
            'period_start' => $periodStart->format('Y-m-d'),
            'closing_date' => $closingDate,
            'confirmed_at' => $closing['confirmed_at'] ?? null,
            'receipt_path' => $closing['receipt_path'] ?? null,
            'receipt_filename' => $closing['receipt_filename'] ?? null,
            'closing_supported' => $closing['supported'] ?? false,
            'closing_error' => $closing['error'] ?? null,
            'rate' => $rate,
            'base_salary' => $baseMonthly,
            'today_total' => $todayTotal,
            'month_total' => $monthTotal,
            'month_total_with_base' => $baseMonthly + $monthTotal,
        ]);
    }

    public function adminOverview(ServerRequestInterface $request): ResponseInterface
    {
        $query = $this->query($request);
        $vendorId = isset($query['vendor_id']) ? trim((string) $query['vendor_id']) : '';
        if ($vendorId === '') {
            return $this->error('VALIDATION_ERROR', 'Vendor_id e obrigatorio.', 422);
        }

        $now = new \DateTimeImmutable('now');
        $rate = self::COMMISSION_RATE;
        $baseMonthly = self::BASE_SALARY;

        $lifetimeTotal = $this->calculateCommissionTotal($vendorId, null, null, $rate);

        $lastClosing = $this->fetchLastClosing($vendorId);
        $lastClosedTotal = 0.0;
        $lastClosedStart = null;
        $lastClosedEnd = null;
        $receiptPath = null;

        if (isset($lastClosing['period_start'], $lastClosing['closing_date'])) {
            $lastClosedStart = new \DateTimeImmutable($lastClosing['period_start']);
            $lastClosedEnd = new \DateTimeImmutable($lastClosing['closing_date']);
            $lastClosedTotal = $this->calculateCommissionTotal($vendorId, $lastClosedStart, $lastClosedEnd, $rate);
            $receiptPath = $lastClosing['receipt_path'] ?? null;
        }

        $currentStart = $lastClosedEnd !== null
            ? $lastClosedEnd->modify('+1 day')
            : new \DateTimeImmutable('first day of this month');
        $currentEnd = $now;
        $currentTotal = $this->calculateCommissionTotal($vendorId, $currentStart, $currentEnd, $rate);

        $recentSales = $this->fetchRecentSales($vendorId, $rate);

        return $this->json([
            'vendor_id' => $vendorId,
            'rate' => $rate,
            'base_salary' => $baseMonthly,
            'lifetime_total' => $lifetimeTotal,
            'last_closed_total' => $lastClosedTotal,
            'last_closed_period_start' => $lastClosedStart?->format('Y-m-d'),
            'last_closed_period_end' => $lastClosedEnd?->format('Y-m-d'),
            'current_total' => $currentTotal,
            'current_period_start' => $currentStart->format('Y-m-d'),
            'current_period_end' => $currentEnd->format('Y-m-d'),
            'recent_sales' => $recentSales,
            'receipt_path' => $receiptPath,
        ]);
    }

    public function adminConfirm(ServerRequestInterface $request): ResponseInterface
    {
        $payload = $this->input($request);
        $vendorId = isset($payload['vendor_id']) ? trim((string) $payload['vendor_id']) : '';
        if ($vendorId === '') {
            return $this->error('VALIDATION_ERROR', 'Vendor_id e obrigatorio.', 422);
        }

        $closingDate = $this->resolveClosingDate($payload['closing_date'] ?? null);
        $lastClosing = $this->fetchLastClosing($vendorId);
        $lastClosingDate = isset($lastClosing['closing_date'])
            ? new \DateTimeImmutable($lastClosing['closing_date'])
            : null;

        if ($lastClosingDate !== null && $closingDate <= $lastClosingDate) {
            return $this->error(
                'VALIDATION_ERROR',
                sprintf(
                    'Data de fechamento deve ser maior que o ultimo fechamento (%s).',
                    $lastClosingDate->format('d/m/Y')
                ),
                422
            );
        }

        $periodStart = $lastClosingDate !== null
            ? $lastClosingDate->modify('+1 day')
            : $closingDate->modify('first day of this month');

        $saved = $this->upsertClosing($vendorId, $periodStart, [
            'closing_date' => $closingDate->format('Y-m-d'),
            'confirmed_at' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ]);
        if (!($saved['ok'] ?? false)) {
            return $this->error(
                'CLOSING_UNAVAILABLE',
                'Fechamento nao configurado. Verifique a tabela commission_closings.',
                500
            );
        }

        return $this->json([
            'vendor_id' => $vendorId,
            'period_start' => $periodStart->format('Y-m-d'),
            'closing_date' => $saved['closing_date'] ?? $closingDate->format('Y-m-d'),
            'confirmed_at' => $saved['confirmed_at'] ?? (new \DateTimeImmutable())->format(DATE_ATOM),
        ]);
    }

    public function adminUpload(ServerRequestInterface $request): ResponseInterface
    {
        $payload = $request->getParsedBody();
        $vendorId = is_array($payload) && isset($payload['vendor_id']) ? trim((string) $payload['vendor_id']) : '';
        if ($vendorId === '') {
            return $this->error('VALIDATION_ERROR', 'Vendor_id e obrigatorio.', 422);
        }

        $closingDate = $this->resolveClosingDate(is_array($payload) ? ($payload['closing_date'] ?? null) : null);
        $periodStart = $this->resolveMonthStart($closingDate->format('Y-m'));

        $uploaded = $request->getUploadedFiles();
        $file = $uploaded['receipt'] ?? null;
        if (!$file instanceof UploadedFileInterface) {
            return $this->error('VALIDATION_ERROR', 'Comprovante nao enviado.', 422);
        }

        if ($file->getError() !== UPLOAD_ERR_OK) {
            return $this->error('UPLOAD_ERROR', 'Falha ao receber o comprovante.', 422);
        }

        $filename = $file->getClientFilename() ?? 'comprovante';
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed = ['pdf', 'png', 'jpg', 'jpeg'];
        if ($ext === '' || !in_array($ext, $allowed, true)) {
            return $this->error('VALIDATION_ERROR', 'Tipo de arquivo nao permitido.', 422);
        }

        $safeVendor = preg_replace('/[^a-zA-Z0-9]/', '', $vendorId) ?: 'vendor';
        $baseName = sprintf(
            'comissao_%s_%s_%s.%s',
            substr($safeVendor, 0, 8),
            $closingDate->format('Ymd'),
            date('His'),
            $ext
        );

        $rootPath = dirname(__DIR__, 3);
        $storageDir = rtrim($rootPath, DIRECTORY_SEPARATOR) . self::RECEIPT_DIR;
        if (!is_dir($storageDir) && !mkdir($storageDir, 0775, true) && !is_dir($storageDir)) {
            return $this->error('UPLOAD_ERROR', 'Falha ao preparar diretorio de comprovantes.', 500);
        }

        $targetPath = $storageDir . DIRECTORY_SEPARATOR . $baseName;

        try {
            $file->moveTo($targetPath);
        } catch (\Throwable) {
            return $this->error('UPLOAD_ERROR', 'Falha ao salvar comprovante.', 500);
        }

        $receiptPath = self::RECEIPT_WEB_PATH . '/' . $baseName;
        $saved = $this->upsertClosing($vendorId, $periodStart, [
            'closing_date' => $closingDate->format('Y-m-d'),
            'receipt_path' => $receiptPath,
            'receipt_filename' => $baseName,
        ]);
        if (!($saved['ok'] ?? false)) {
            return $this->error(
                'CLOSING_UNAVAILABLE',
                'Comprovante salvo, mas o fechamento nao foi registrado. Verifique a tabela commission_closings.',
                500
            );
        }

        $vendorName = $this->fetchVendorName($vendorId) ?? $vendorId;
        $totals = $this->calculateMonthTotals($vendorId, $periodStart);
        $subject = sprintf('Comissao #%s', $closingDate->format('Y-m-d'));
        $html = $this->buildReceiptEmail($vendorName, $closingDate, $totals, $receiptPath);
        $text = strip_tags($html);

        $emailWarning = null;
        if ($this->isMailConfigured()) {
            try {
                $this->mailer->sendWithAttachment(
                    'lucasgonju@gmail.com',
                    $subject,
                    $html,
                    $text,
                    $targetPath,
                    $baseName
                );
            } catch (\Throwable $error) {
                $emailWarning = 'Comprovante salvo, mas falha ao enviar email.';
                error_log('[COMMISSION] Email send failed: ' . $error->getMessage());
            }
        } else {
            $emailWarning = 'Comprovante salvo, mas o email nao esta configurado.';
        }

        return $this->json([
            'vendor_id' => $vendorId,
            'period_start' => $periodStart->format('Y-m-d'),
            'closing_date' => $closingDate->format('Y-m-d'),
            'receipt_path' => $saved['receipt_path'] ?? $receiptPath,
            'receipt_filename' => $saved['receipt_filename'] ?? $baseName,
            'email_warning' => $emailWarning,
        ]);
    }

    /**
     * @return array{today_total: float, month_total: float, base_salary: float, total_with_base: float}
     */
    private function calculateMonthTotals(string $vendorId, \DateTimeImmutable $periodStart): array
    {
        $periodEnd = $periodStart->modify('last day of this month');
        $today = new \DateTimeImmutable('today');

        $todayTotal = 0.0;
        $monthTotal = 0.0;
        $rate = self::COMMISSION_RATE;
        $baseSalary = self::BASE_SALARY;

        $sales = $this->sales->search([
            'user_id' => $vendorId,
            'from' => $periodStart->setTime(0, 0, 0)->format(DATE_ATOM),
            'to' => $periodEnd->setTime(23, 59, 59)->format(DATE_ATOM),
        ]);

        foreach ($sales as $sale) {
            if (!in_array($sale->status(), [SaleStatus::PAGA, SaleStatus::ENTREGUE], true)) {
                continue;
            }

            $commissionForSale = $sale->subtotal()->toFloat() * $rate;
            $createdDate = $sale->createdAt()->format('Y-m-d');

            $monthTotal += $commissionForSale;
            if ($createdDate === $today->format('Y-m-d')) {
                $todayTotal += $commissionForSale;
            }
        }

        return [
            'today_total' => $todayTotal,
            'month_total' => $monthTotal,
            'base_salary' => $baseSalary,
            'total_with_base' => $baseSalary + $monthTotal,
        ];
    }

    /**
     * @return array<string, string|null>
     */
    private function fetchClosing(string $vendorId, \DateTimeImmutable $periodStart): array
    {
        try {
            $response = $this->supabase->runWithServiceRole(function () use ($vendorId, $periodStart) {
                return $this->supabase->request('GET', 'rest/v1/commission_closings', [
                    'headers' => ['Prefer' => 'single-object'],
                    'query' => [
                        'vendor_id' => 'eq.' . $vendorId,
                        'period_start' => 'eq.' . $periodStart->format('Y-m-d'),
                    ],
                ]);
            });
        } catch (\Throwable $error) {
            return [
                'supported' => false,
                'error' => $error->getMessage(),
            ];
        }

        if ($response === null) {
            return [
                'supported' => true,
            ];
        }

        if (!is_array($response)) {
            return [
                'supported' => true,
            ];
        }

        $row = array_is_list($response) ? ($response[0] ?? []) : $response;
        if (!is_array($row)) {
            return [
                'supported' => true,
            ];
        }

        return [
            'supported' => true,
            'closing_date' => is_string($row['closing_date'] ?? null) ? $row['closing_date'] : null,
            'confirmed_at' => is_string($row['confirmed_at'] ?? null) ? $row['confirmed_at'] : null,
            'receipt_path' => is_string($row['receipt_path'] ?? null) ? $row['receipt_path'] : null,
            'receipt_filename' => is_string($row['receipt_filename'] ?? null) ? $row['receipt_filename'] : null,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function fetchLastClosing(string $vendorId): array
    {
        try {
            $response = $this->supabase->runWithServiceRole(function () use ($vendorId) {
                return $this->supabase->request('GET', 'rest/v1/commission_closings', [
                    'query' => [
                        'vendor_id' => 'eq.' . $vendorId,
                        'order' => 'closing_date.desc',
                        'limit' => '1',
                    ],
                ]);
            });
        } catch (\Throwable) {
            return [];
        }

        if (!is_array($response)) {
            return [];
        }

        $row = array_is_list($response) ? ($response[0] ?? []) : $response;
        if (!is_array($row)) {
            return [];
        }

        $periodStart = is_string($row['period_start'] ?? null) ? $row['period_start'] : null;
        $closingDate = is_string($row['closing_date'] ?? null) ? $row['closing_date'] : null;
        $receiptPath = is_string($row['receipt_path'] ?? null) ? $row['receipt_path'] : null;
        $receiptFilename = is_string($row['receipt_filename'] ?? null) ? $row['receipt_filename'] : null;

        if ($periodStart === null || $closingDate === null) {
            return [];
        }

        return [
            'period_start' => $periodStart,
            'closing_date' => $closingDate,
            'receipt_path' => $receiptPath,
            'receipt_filename' => $receiptFilename,
        ];
    }

    /**
     * @param array<string, string> $data
     * @return array<string, string|null>
     */
    private function upsertClosing(string $vendorId, \DateTimeImmutable $periodStart, array $data): array
    {
        $payload = [
            'vendor_id' => $vendorId,
            'period_start' => $periodStart->format('Y-m-d'),
        ];

        foreach ($data as $key => $value) {
            $payload[$key] = $value;
        }

        try {
            $response = $this->supabase->runWithServiceRole(function () use ($payload) {
                return $this->supabase->request('POST', 'rest/v1/commission_closings', [
                    'json' => [$payload],
                    'headers' => ['Prefer' => 'return=representation,resolution=merge-duplicates'],
                ]);
            });
        } catch (\Throwable $error) {
            return [
                'ok' => false,
                'error' => $error->getMessage(),
            ];
        }

        if (!is_array($response)) {
            return [
                'ok' => false,
                'error' => 'Resposta invalida do Supabase.',
            ];
        }

        $row = array_is_list($response) ? ($response[0] ?? []) : $response;
        if (!is_array($row)) {
            return [
                'ok' => false,
                'error' => 'Resposta invalida do Supabase.',
            ];
        }

        return [
            'ok' => true,
            'closing_date' => is_string($row['closing_date'] ?? null) ? $row['closing_date'] : null,
            'confirmed_at' => is_string($row['confirmed_at'] ?? null) ? $row['confirmed_at'] : null,
            'receipt_path' => is_string($row['receipt_path'] ?? null) ? $row['receipt_path'] : null,
            'receipt_filename' => is_string($row['receipt_filename'] ?? null) ? $row['receipt_filename'] : null,
        ];
    }

    private function resolveMonthStart(string $monthParam): \DateTimeImmutable
    {
        if ($monthParam !== '' && preg_match('/^\d{4}-\d{2}$/', $monthParam)) {
            return new \DateTimeImmutable($monthParam . '-01');
        }

        return new \DateTimeImmutable('first day of this month');
    }

    private function resolveClosingDate(mixed $value): \DateTimeImmutable
    {
        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return new \DateTimeImmutable($value);
        }

        return new \DateTimeImmutable('today');
    }

    private function calculateCommissionTotal(
        string $vendorId,
        ?\DateTimeImmutable $from,
        ?\DateTimeImmutable $to,
        float $rate
    ): float {
        $filters = [
            'user_id' => $vendorId,
        ];

        if ($from !== null) {
            $filters['from'] = $from->setTime(0, 0, 0)->format(DATE_ATOM);
        }
        if ($to !== null) {
            $filters['to'] = $to->setTime(23, 59, 59)->format(DATE_ATOM);
        }

        $sales = $this->sales->search($filters);
        $total = 0.0;

        foreach ($sales as $sale) {
            if (!in_array($sale->status(), [SaleStatus::PAGA, SaleStatus::ENTREGUE], true)) {
                continue;
            }

            $total += $sale->subtotal()->toFloat() * $rate;
        }

        return $total;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchRecentSales(string $vendorId, float $rate): array
    {
        $sales = $this->sales->search([
            'user_id' => $vendorId,
            'limit' => 50,
        ]);

        $items = [];
        foreach ($sales as $sale) {
            if (!in_array($sale->status(), [SaleStatus::PAGA, SaleStatus::ENTREGUE], true)) {
                continue;
            }

            $items[] = [
                'id' => $sale->id(),
                'created_at' => $sale->createdAt()->format(DATE_ATOM),
                'status' => $sale->status()->value,
                'subtotal' => $sale->subtotal()->toFloat(),
                'commission' => $sale->subtotal()->toFloat() * $rate,
            ];

            if (count($items) >= 10) {
                break;
            }
        }

        return $items;
    }

    private function isMailConfigured(): bool
    {
        $host = trim((string) ($_ENV['MAIL_HOST'] ?? ''));
        $username = trim((string) ($_ENV['MAIL_USERNAME'] ?? ''));
        $password = trim((string) ($_ENV['MAIL_PASSWORD'] ?? ''));
        $from = trim((string) ($_ENV['MAIL_FROM_ADDRESS'] ?? ''));

        if ($host === '' || $username === '' || $password === '' || $from === '') {
            return false;
        }

        return true;
    }

    private function extractRoleName(mixed $roles): ?string
    {
        if (is_array($roles)) {
            if (array_is_list($roles)) {
                foreach ($roles as $role) {
                    if (is_array($role) && is_string($role['name'] ?? null)) {
                        return $role['name'];
                    }
                }
            }

            if (is_string($roles['name'] ?? null)) {
                return $roles['name'];
            }
        }

        return null;
    }

    private function fetchVendorName(string $vendorId): ?string
    {
        try {
            $response = $this->supabase->runWithServiceRole(function () use ($vendorId) {
                return $this->supabase->request('GET', 'rest/v1/users', [
                    'headers' => ['Prefer' => 'single-object'],
                    'query' => [
                        'select' => 'id,name,email',
                        'id' => 'eq.' . $vendorId,
                    ],
                ]);
            });
        } catch (\Throwable) {
            return null;
        }

        if (!is_array($response)) {
            return null;
        }

        $row = array_is_list($response) ? ($response[0] ?? []) : $response;
        if (!is_array($row)) {
            return null;
        }

        return is_string($row['name'] ?? null) ? $row['name'] : null;
    }

    /**
     * @param array{today_total: float, month_total: float, base_salary: float, total_with_base: float} $totals
     */
    private function buildReceiptEmail(string $vendorName, \DateTimeImmutable $closingDate, array $totals, string $receiptPath): string
    {
        $todayTotal = number_format($totals['today_total'], 2, ',', '.');
        $monthTotal = number_format($totals['month_total'], 2, ',', '.');
        $baseSalary = number_format($totals['base_salary'], 2, ',', '.');
        $totalWithBase = number_format($totals['total_with_base'], 2, ',', '.');
        $dateLabel = $closingDate->format('d/m/Y');

        return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>Comissao</title>
    <style>
        body { font-family: Arial, sans-serif; color: #1e293b; background: #f8fafc; padding: 24px; }
        h1 { color: #1d4ed8; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { padding: 8px; border-bottom: 1px solid #e2e8f0; }
        th { text-align: left; background: #eff6ff; }
    </style>
</head>
<body>
    <h1>Fechamento de comissao</h1>
    <p>Vendedora: <strong>{$vendorName}</strong></p>
    <p>Data do pagamento: <strong>{$dateLabel}</strong></p>
    <table>
        <tr><th>Comissao de hoje</th><td>R$ {$todayTotal}</td></tr>
        <tr><th>Comissao do mes</th><td>R$ {$monthTotal}</td></tr>
        <tr><th>Remuneracao base</th><td>R$ {$baseSalary}</td></tr>
        <tr><th>Total (base + comissao)</th><td>R$ {$totalWithBase}</td></tr>
        <tr><th>Arquivo</th><td>{$receiptPath}</td></tr>
    </table>
</body>
</html>
HTML;
    }
}
