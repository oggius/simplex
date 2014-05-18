<?php
namespace system\pagination;

use system\basic\exceptions\BadClassInstantiationException;

/**
 * Class PaginationFactory
 * @package system\pagination
 */
class PaginationFactory {

    /**
     * @param $paginationType
     * @param string $pageId
     * @return Pagination
     * @throws \system\basic\exceptions\BadClassInstantiationException
     */
    public static function factory($paginationType, $pageId = 'page')
    {
        $className = __NAMESPACE__ . '\\paginator\\' . ucfirst($paginationType) . 'Pagination';
        $pagination = new $className();
        if ($pagination instanceof Pagination) {
            return $pagination;
        } else {
            throw new BadClassInstantiationException('Pagination ' . $paginationType . ' was not initialised correctly');
        }
    }
}