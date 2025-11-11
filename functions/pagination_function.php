<?php
/**
 * Pagination helper functions
 */

/**
 * Calculate pagination info
 * @param int $totalItems Total number of items
 * @param int $currentPage Current page number
 * @param int $itemsPerPage Items per page
 * @return array Pagination info
 */
function get_pagination_info($totalItems, $currentPage = 1, $itemsPerPage = 10) {
    $currentPage = max(1, intval($currentPage));
    $itemsPerPage = max(1, intval($itemsPerPage));
    $totalPages = max(1, ceil($totalItems / $itemsPerPage));
    $currentPage = min($currentPage, $totalPages);
    
    $offset = ($currentPage - 1) * $itemsPerPage;
    
    return [
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'total_items' => $totalItems,
        'items_per_page' => $itemsPerPage,
        'offset' => $offset,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages,
        'previous_page' => $currentPage > 1 ? $currentPage - 1 : null,
        'next_page' => $currentPage < $totalPages ? $currentPage + 1 : null,
        'start_item' => $totalItems > 0 ? $offset + 1 : 0,
        'end_item' => min($offset + $itemsPerPage, $totalItems)
    ];
}

/**
 * Generate pagination URL
 * @param string $baseUrl Base URL
 * @param int $page Page number
 * @param array $params Additional query parameters
 * @return string Pagination URL
 */
function pagination_url($baseUrl, $page, $params = []) {
    $params['page'] = $page;
    $queryString = http_build_query($params);
    return $baseUrl . ($queryString ? '?' . $queryString : '');
}

/**
 * Get page numbers to display in pagination
 * @param int $currentPage Current page
 * @param int $totalPages Total pages
 * @param int $maxPages Maximum pages to show
 * @return array Page numbers
 */
function get_pagination_pages($currentPage, $totalPages, $maxPages = 7) {
    $pages = [];
    
    if ($totalPages <= $maxPages) {
        // Show all pages if total is less than max
        for ($i = 1; $i <= $totalPages; $i++) {
            $pages[] = $i;
        }
    } else {
        // Show pages around current page
        $half = floor($maxPages / 2);
        $start = max(1, $currentPage - $half);
        $end = min($totalPages, $start + $maxPages - 1);
        
        // Adjust start if we're near the end
        if ($end - $start < $maxPages - 1) {
            $start = max(1, $end - $maxPages + 1);
        }
        
        // Always show first page
        if ($start > 1) {
            $pages[] = 1;
            if ($start > 2) {
                $pages[] = '...';
            }
        }
        
        // Show pages in range
        for ($i = $start; $i <= $end; $i++) {
            $pages[] = $i;
        }
        
        // Always show last page
        if ($end < $totalPages) {
            if ($end < $totalPages - 1) {
                $pages[] = '...';
            }
            $pages[] = $totalPages;
        }
    }
    
    return $pages;
}


