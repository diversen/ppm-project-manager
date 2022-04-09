<?php

namespace App\Utils;

class AppPagination
{
    public function render(\JasonGrimes\Paginator $paginator) { ?>

        <div class="pagination"><?php

        $num_pages = count($paginator->getPages());

        if ($num_pages):

            if ($paginator->getPrevUrl()): ?>
                <a class='pagination-links' href='<?=$paginator->getPrevUrl()?>'>&lt;&lt;</a><?php
            else: ?>
                <span class='pagination-links'>&lt;&lt;</span><?php
            endif;

            foreach ($paginator->getPages() as $page):

                $css_class = 'pagination-links';
                if ($page['url']):
                    if ($page['isCurrent']):
                        $css_class = 'pagination-links pagination-current';
                    endif; ?>
                    <a class="<?=$css_class?>" href="<?=$page['url']; ?>"><?=$page['num']; ?></a><?php
                else: ?>
                    <span class="<?=$css_class?>" disabled><?php echo $page['num']; ?></span>
                <?php endif; ?>
            <?php

            endforeach;

            if ($paginator->getNextUrl()):?>
                <a class='pagination-links' href='<?=$paginator->getNextUrl()?>'>&gt;&gt;</a><?php
            else: ?>
                <span class='pagination-links'>&gt;&gt;</span><?php
            endif; ?>        
            </div><?php
        endif;
    }
}
