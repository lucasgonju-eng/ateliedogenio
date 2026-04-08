<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Inventory;

final class ProductSizeCatalog
{
    public const GROUP_FEMININA = 'feminina';
    public const GROUP_MASCULINA = 'masculina';

    /**
     * @return array<int, string>
     */
    public static function feminine(): array
    {
        return ['PI', 'MI', 'GI', 'PP', 'P', 'M', 'G', 'GG', 'EXG', 'EXGG'];
    }

    /**
     * @return array<int, string>
     */
    public static function masculine(): array
    {
        return ['12', '14'];
    }

    /**
     * Valores antigos que ainda podem existir no banco.
     *
     * @return array<int, string>
     */
    public static function legacy(): array
    {
        return ['12 anos', '14 anos', 'XG'];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function grouped(): array
    {
        return [
            self::GROUP_FEMININA => self::feminine(),
            self::GROUP_MASCULINA => self::masculine(),
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        $all = array_merge(self::feminine(), self::masculine(), self::legacy());
        $unique = array_values(array_unique($all));

        return $unique;
    }
}
