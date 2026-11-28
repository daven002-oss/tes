<?php
/* "Showing 1-10 of N" + page numbers. Needs $total, $per, $page, $pages, $q and $pagerBase (the page file name). */
$pagerBase = $pagerBase ?? basename($_SERVER['SCRIPT_NAME']);
$from = $total ? ($page - 1) * $per + 1 : 0;
$to   = min($total, $page * $per);
$link = function (int $n) use ($pagerBase, $q): string {
    return e($pagerBase . '?' . http_build_query(['q' => $q, 'page' => $n]));
};
?>
<div class="pager">
    <span class="table-foot">Showing <?= $from ?>&ndash;<?= $to ?> of <?= $total ?></span>
    <?php if ($pages > 1): ?>
        <nav aria-label="Pages">
            <?php for ($n = 1; $n <= $pages; $n++): ?>
                <a class="<?= $n === $page ? 'on' : '' ?>" href="<?= $link($n) ?>"<?= $n === $page ? ' aria-current="page"' : '' ?>><?= $n ?></a>
            <?php endfor; ?>
            <?php if ($page < $pages): ?><a class="next" href="<?= $link($page + 1) ?>">Next &rarr;</a><?php endif; ?>
        </nav>
    <?php endif; ?>
</div>
