<!-- Vertical navbar -->
<div class="vertical-nav" id="sidebar">
    <div class="py-4 px-3 mb-4 sidebar-logo">
        <a href="<?= URLROOT; ?>">
            <img src="<?= URLROOT.'/img/Logo.png'; ?>" alt="..." class="me-3 w-100">
        </a>
    </div>
    <?php
    $controller = new Controller;
    $controller->generalModel = $controller->model('General');
    $menuItems = $controller->generalModel->getItemsWithSort("menu","sort","asc");
    ?>
    <?php if(isLoggedIn()): ?>
        <ul class="nav flex-column mb-0">
            <?php foreach($menuItems as $menuItem): ?>
                <?php 

                $userType = getUserType();
                if(!hasRight($userType, $menuItem->can_view)) {
                    continue;
                }
                //we create the separator classes if needed
                if($menuItem->separator_start == 1):
                    $separatorClass = "border-light border-top pt-3 mt-2";
                else:
                    $separatorClass = "";
                endif;

                //we create the submenu if needed
                if($menuItem->start_sub_menu == 1):
                    $subNavClass = "js-nav-has-sub-nav";
                else:
                    $subNavClass = "";
                endif;
                ?>
                <li class="nav-item mb-2 <?= $separatorClass." ".$subNavClass ?>">
                    <a href="<?= URLROOT."/".$menuItem->link; ?>" class="nav-link text-light font-italic">
                        <i class="bi bi-<?= $menuItem->icon; ?>"></i>
                        <span class="ms-3 nav-item-text"><?= $menuItem->displayName; ?></span>
                    </a>
                </li>

                <?php 
                //we create the submenu if needed
                if($menuItem->start_sub_menu == 1):
                    echo '<ul class="nav sub-nav flex-column mb-0">';
                endif;
                if($menuItem->end_sub_menu == 1):
                     echo '</ul>';
                endif;
                ?>

            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
<!-- End vertical navbar -->