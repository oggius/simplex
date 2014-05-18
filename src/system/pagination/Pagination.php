<?php
namespace system\pagination;

abstract class Pagination implements \Iterator {
    /**
     * @var int iterator position
     */
    protected $_position = 0;

    protected $_currentPage;
    protected $_totalItems;
    protected $_itemsPerPage;
    protected $_paginationItemsPerRow = 5;
    protected $_basicUrl;
    protected $_pageId = 'page';

    protected $_paginationItems = array();


    abstract public function build();

    /**
     * @param $pageId
     * @param $pageNumber
     * @return string
     */
    protected function _buildLink($pageId, $pageNumber)
    {
        if ($pageNumber > 1) {
            return $this->_basicUrl . $pageId . '/' . $pageNumber . '/';
        } else {
            return $this->_basicUrl;
        }
    }

    /**
     * @param $pageNumber
     */
    public function setCurrentPage($pageNumber)
    {
        $this->_currentPage = (int)$pageNumber;
    }

    /**
     * @param int $totalItems
     */
    public function setTotalItems($totalItems)
    {
        $this->_totalItems = (int)$totalItems;
    }

    /**
     * @param int $perPage
     */
    public function setItemsPerPage($perPage)
    {
        $this->_itemsPerPage = $perPage;
    }

    /**
     * @param int $itemsCount
     */
    public function setPaginationItemsPerRow($itemsCount)
    {
        $this->_paginationItemsPerRow = $itemsCount;
    }

    /**
     * @param string $basicUrl
     */
    public function setBasicUrl($basicUrl)
    {
        $this->_basicUrl = $basicUrl;
    }

    /**
     * @param $pageId
     */
    public function setPageId($pageId)
    {
        $this->_pageId = $pageId;
    }

    /**
     * @param $pageNumber
     * @param $itemsCount
     * @param $perPage
     * @param $basicUrl
     */
    public function setParams($pageNumber, $itemsCount, $perPage, $basicUrl)
    {
        $this->setCurrentPage($pageNumber);
        $this->setTotalItems($itemsCount);
        $this->setItemsPerPage($perPage);
        $this->setBasicUrl($basicUrl);
        return;
    }

    /**
     *
     */
    public function rewind() {
        $this->position = 0;
    }

    /**
     * @return mixed
     */
    public function current() {
        return $this->_paginationItems[$this->position];
    }

    /**
     * @return mixed
     */
    public function key() {
        return $this->position;
    }

    /**
     * move position forward
     */
    public function next() {
        ++$this->position;
    }

    /**
     * @return bool
     */
    public function valid() {
        return isset($this->_paginationItems[$this->position]);
    }

    /**
     * returns total pages count
     * @return float
     */
    public function getTotalPages()
    {
        return ceil($this->_totalItems / $this->_itemsPerPage);
    }

}