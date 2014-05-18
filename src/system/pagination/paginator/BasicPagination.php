<?php
namespace system\pagination\paginator;

use system\basic\exceptions\WrongInputParamsException;
use system\pagination\Pagination;
use system\pagination\PaginationItem;

/**
 * Class BasicPagination
 * represents pagination of the following style: 1 ... 6, 7, 8, 9, 10 ... 15
 * @package system\pagination\paginator
 */
class BasicPagination extends Pagination
{
    /**
     * @return Pagination
     * @throws \system\basic\exceptions\WrongInputParamsException
     */
    public function build()
    {
        if ($this->_totalItems == 0) {
            return null;
        }

        if (!$this->_currentPage || !$this->_itemsPerPage || !$this->_basicUrl || !$this->_paginationItemsPerRow) {
            throw new WrongInputParamsException('Pagination params are not set', 500);
        }

        $pageId = $this->_pageId ? $this->_pageId : 'page';
        $pagesTotal = ceil($this->_totalItems / $this->_itemsPerPage);
        if ($this->_currentPage > $pagesTotal || $this->_currentPage < 0) {
            $this->_currentPage = 0;
        }
        $extraItems = floor($this->_paginationItemsPerRow / 2);
        if (($this->_currentPage - $extraItems) > 3) {
            $separatorBefore = true;
            $iterationsStart = $this->_currentPage - $extraItems;
            /*
            // add additional offset for the first pages
            if ($this->_currentPage > $pagesTotal - $extraItems) {
                $iterationsStart -= $pagesTotal - $this->_currentPage;
            }
            */
        } else if ($pagesTotal < 2) {
            $separatorBefore = false;
            $iterationsStart = 0;
        } else {
            $separatorBefore = false;
            $iterationsStart = 2;
        }

        if (($this->_currentPage + $extraItems) < $pagesTotal - 2) {
            $separatorAfter = true;
            $iterationsEnd = $this->_currentPage + $extraItems;
            /*
            // add additional offset for the first pages
            if ($this->_currentPage < $extraItems + 1) {
                $iterationsEnd += $extraItems + 1 - $this->_currentPage;
            }
            */
        } elseif ($pagesTotal < 2) {
            $separatorAfter = false;
            $iterationsEnd = 0;
        } else {
            $separatorAfter = false;
            $iterationsEnd = $pagesTotal;
        }

        // start building pagination itself
        $this->_paginationItems[] = new PaginationItem(
                                                'page',
                                                '1',
                                                $this->_buildLink($pageId, 1),
                                                $this->_currentPage == 1 ? true : false
                                        );
        if ($separatorBefore) {
            $this->_paginationItems[] = new PaginationItem('separator', '...');
        }
        if (!empty($iterationsStart) && !empty($iterationsEnd)) {
            foreach(range($iterationsStart, $iterationsEnd) as $pageNumber) {
                $this->_paginationItems[] = new PaginationItem(
                                                        'page',
                                                        $pageNumber,
                                                        $this->_buildLink($pageId, $pageNumber),
                                                        $this->_currentPage == $pageNumber ? true : false
                                            );
            }
        }
        if ($separatorAfter) {
            $this->_paginationItems[] = new PaginationItem('separator', '...');
        }
        if ($iterationsEnd && $iterationsEnd != $pagesTotal) {
            $this->_paginationItems[] = new PaginationItem(
                                                    'page',
                                                    $pagesTotal,
                                                    $this->_buildLink($pageId, $pagesTotal),
                                                    $this->_currentPage == $pagesTotal ? true : false
                                                    );
        }
        return $this;
    }
}