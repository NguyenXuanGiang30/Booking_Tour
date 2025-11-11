<?php
/**
 * Pagination Component
 * @param array $pagination Pagination info from get_pagination_info()
 * @param string $baseUrl Base URL for pagination links
 * @param array $queryParams Additional query parameters to preserve
 */
if (!isset($pagination) || !isset($baseUrl)) {
    return;
}

$queryParams = $queryParams ?? [];
$currentPage = $pagination['current_page'];
$totalPages = $pagination['total_pages'];
$totalItems = $pagination['total_items'];
$startItem = $pagination['start_item'];
$endItem = $pagination['end_item'];

if ($totalPages <= 1) {
    return; // Don't show pagination if only one page
}

$pages = get_pagination_pages($currentPage, $totalPages);
?>
<div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 mt-6">
    <div class="flex flex-1 justify-between sm:hidden">
        <?php if ($pagination['has_previous']): ?>
            <a href="<?= pagination_url($baseUrl, $pagination['previous_page'], $queryParams) ?>" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Previous
            </a>
        <?php else: ?>
            <span class="relative inline-flex items-center rounded-md border border-gray-300 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-400 cursor-not-allowed">
                Previous
            </span>
        <?php endif; ?>
        
        <?php if ($pagination['has_next']): ?>
            <a href="<?= pagination_url($baseUrl, $pagination['next_page'], $queryParams) ?>" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Next
            </a>
        <?php else: ?>
            <span class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-400 cursor-not-allowed">
                Next
            </span>
        <?php endif; ?>
    </div>
    
    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-gray-700">
                Showing <span class="font-medium"><?= $startItem ?></span> to <span class="font-medium"><?= $endItem ?></span> of <span class="font-medium"><?= $totalItems ?></span> results
            </p>
        </div>
        <div>
            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                <?php if ($pagination['has_previous']): ?>
                    <a href="<?= pagination_url($baseUrl, $pagination['previous_page'], $queryParams) ?>" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                        <span class="sr-only">Previous</span>
                        <i class="fas fa-chevron-left h-5 w-5"></i>
                    </a>
                <?php else: ?>
                    <span class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-300 ring-1 ring-inset ring-gray-300 cursor-not-allowed">
                        <span class="sr-only">Previous</span>
                        <i class="fas fa-chevron-left h-5 w-5"></i>
                    </span>
                <?php endif; ?>
                
                <?php foreach ($pages as $page): ?>
                    <?php if ($page === '...'): ?>
                        <span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300">...</span>
                    <?php elseif ($page == $currentPage): ?>
                        <span aria-current="page" class="relative z-10 inline-flex items-center bg-blue-600 px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                            <?= $page ?>
                        </span>
                    <?php else: ?>
                        <a href="<?= pagination_url($baseUrl, $page, $queryParams) ?>" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                            <?= $page ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
                
                <?php if ($pagination['has_next']): ?>
                    <a href="<?= pagination_url($baseUrl, $pagination['next_page'], $queryParams) ?>" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                        <span class="sr-only">Next</span>
                        <i class="fas fa-chevron-right h-5 w-5"></i>
                    </a>
                <?php else: ?>
                    <span class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-300 ring-1 ring-inset ring-gray-300 cursor-not-allowed">
                        <span class="sr-only">Next</span>
                        <i class="fas fa-chevron-right h-5 w-5"></i>
                    </span>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</div>


