<?php

declare(strict_types=1);


namespace WEBcoast\VersatileCrawler\Tca;


class TableSelectItems
{
    public function addTableItems(&$parameters): void
    {
        $items = &$parameters['items'];

        foreach ($GLOBALS['TCA'] as $table => $configuration) {
            if (in_array($table, ['pages', 'tt_content', 'sys_category']) || str_starts_with($table, 'tx_')) {
                $items[] = [
                    'label' => $configuration['ctrl']['title'],
                    'value' => $table
                ];
            }
        }
    }
}
