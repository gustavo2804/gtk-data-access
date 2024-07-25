<?php

class CellContent
{
    public $column;
    public $cellClass;
    public $item;
}


class SelectQueryTableGenerator
{
    private SelectQuery $selectQuery;
    private SelectQueryTableOptions $options;

    public function __construct(SelectQuery $selectQuery, SelectQueryTableOptions $options = null)
    {
        $this->selectQuery = $selectQuery;
        $this->options = $options ?? new SelectQueryTableOptions();
    }

    public function generateTable()
    {
        $columnsToDisplay = $this->options->columnsToDisplay ?? $this->selectQuery->columns;
        $itemIndex        = 0;
        ob_start();
        ?>
        <table class="<?php echo $this->options->tableClass; ?>">
            <thead class="<?php echo $this->options->theadClass; ?>">
                <tr>
                    <?php foreach ($columnsToDisplay as $column): ?>
                        <th><?php echo $this->getColumnLabel($column); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody class="<?php echo $this->options->tbodyClass; ?>">
                <?php if (gtk_count($this->selectQuery) == 0): ?>
                    <tr>
                        <td colspan="<?php echo gtk_count($columnsToDisplay); ?>">
                            <?php echo $this->options->noItemsMessage; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($this->selectQuery as $selectQueryItem): ?>
                        <?php
                        
                        $itemStyle = $this->getItemStyleForUserItemIndex($user, $selectQueryItem, $itemIndex);
                        

                        ?>
                        <tr class="<?php echo $this->options->trClass; ?>"
                            <?php if ($this->options->rowStyleCallback): ?>
                                style="<?php echo call_user_func($this->options->rowStyleCallback, $item, $index); ?>"
                            <?php endif; ?>>
                            <?php foreach ($columnsToDisplay as $column): ?>
                                <?php
                                $cellColumnStyle = $this->getCellColumnItemIndexUserStyle($column, $i$item, $user);
                                $cellColumnValue = $this->getColumnValue($selectQueryItem, $column);
                                ?>
                                <td class="<?php echo $this->options->tdClass; ?>">
                                    <?php echo $; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php
                        $itemIndex++;
                        ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }


    public function generatePagination(PaginationStyler $styler)
    {
        $urlBase                = $styler->urlBase                ?? '';
        $pageQueryParameterName = $styler->pageQueryParameterName ?? 'page';
        $paginationLinkClass    = $styler->paginationLinkClass    ?? 'page-link';
        $paginationActiveLinkClass = $styler->paginationActiveLinkClass ?? 'active';
        


        $currentPage = $this->selectQuery->currentPage();
        $totalPages = $this->selectQuery->numberOfPages();

        ob_start();
        ?>
        <div class="<?php echo $this->options->paginationClass; ?>">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php

                $queryParameters =[];
                $queryParameters[$pageQueryParameterName] = $i;
                $queryParameters = array_merge($queryParameters, $styler->extraQueryParameters);

                $linkHref = $urlBase.'?'.http_build_query($queryParameters);
                
                $linkClassTag = [$this->options->paginationLinkClass];

                $isActivePage = ($i == $currentPage);

                if ($isActivePage)
                {
                    $linkClassTag[] = $this->options->paginationActiveLinkClass;
                }

                $linkClassTag = implode(' ', $linkClassTag);

                ?>
    
                <a href="<?php echo $urlBase . '?' . $queryParameterName . '=' . $i; ?>"
                   class="<?php echo $linkClassTag; ?>"
                   style="<?php echo $linkStyleTag; ?>"
                >
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    // ... rest of the class remains the same
}
