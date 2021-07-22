<?php
/**
 * Created by PhpStorm.
 * User: marvi
 * Date: 10.08.2019
 * Time: 14:08
 */
?>

<nav class="navbar navbar-expand-lg navbar-light border-0 rounded fixed-top shadow-sm">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
        <div class="navbar-nav ml-auto">
            <div class="nav-item dropdown">
                <?php $languages = $core->languages(); ?>
                <a class="nav-link dropdown-toggle" href="#" id="dropdown09" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo '<span class="flag-icon flag-icon-' . $_COOKIE['lang'] . '"></span>&nbsp;<span lang="' . $languages[$_COOKIE['lang']] . '"></span>';?></a>
                <div class="dropdown-menu" aria-labelledby="dropdown09">
                    <?php

                    foreach ($languages as $key => $value){
                        echo '<a class="dropdown-item" href="?lang=' . $key . '"><span class="flag-icon flag-icon-' . $key . '"></span>&nbsp;<span lang="' . $value . '"></span></a>';
                    }

                    ?>
                </div>
            </div>
            <div class="nav-item">
                <a class="nav-link" href="https://www.slowloris.de/#contact" target="_blank" lang="contact"></a>
            </div>
            <?php

            if($core->isUserLoggedIn()):
                if($core->getUserData()['user_isAdmin'] == 1):
                ?>
                <div class="nav-item">
                    <a class="nav-link" href="<?=$core->getWebUrl();?>app/admin">Admin</a>
                </div>
                <?php endif ?>
                <div class="nav-item">
                    <a class="nav-link" href="<?=$core->getWebUrl();?>app/dash" lang="dashboard"></a>
                </div>
                <div class="nav-item">
                    <a class="nav-link" href="<?=$core->getWebUrl();?>app/ytdl">YTDL</a>
                </div>
                <div class="nav-item">
                    <a class="nav-link" href="<?=$core->getWebUrl();?>app/notebook" lang="notes"></a>
                </div>
                <div class="nav-item">
                    <a class="nav-link" href="<?=$core->getWebUrl();?>app/logout" lang="logout"></a>
                </div>
            <?php
            else:
                ?>
                <div class="nav-item">
                    <a class="nav-link" href="<?=$core->getWebUrl();?>app/login" lang="login"></a>
                </div>
            <?php
            endif

            ?>
        </div>
    </div>
</nav>
