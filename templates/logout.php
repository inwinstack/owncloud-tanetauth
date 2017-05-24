<?php
style("tanet_auth", "styles");
?>
<ul>
    <li class='update'>
        <?php p($l->t('Logout success.')); ?><br/><br/>
        <a class="button" href="<?php echo \OC_Config::getValue("tanet_login_url") . \OC_Config::getValue("tanet_return_url_key") . "/" ?>"><?php p($l->t("Login again.")) ?></a>
    </li>
</ul>
