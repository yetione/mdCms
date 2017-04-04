<?php require BASE_PATH.'/templates/emails/default/header.php';?>
<table align="center" width="685" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td>
            <table width="685" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td>
                        <span style="font-family: Arial, Helvetica, sans-serif; font-size: 22px; color:#ffffff; line-height: 80px;font-weight: normal;display: block;">Приветствуем, <?php echo $user->getName();?>!</span>
                        <span style="font-family: Arial, Helvetica, sans-serif; font-size: 22px; color:#ffffff; line-height: 80px;font-weight: normal;display: block;">Вы зарегистрировались на сайте $Sektafood.</span>
                        <span style="font-family: Arial, Helvetica, sans-serif; font-size: 22px; color:#ffffff; line-height: 80px;font-weight: normal;display: block;">Ваш логин: <?php echo $user->getLogin()?></span>
                        <?php echo empty($password) ? '' : '<span style="font-family: Arial, Helvetica, sans-serif; font-size: 22px; color:#ffffff; line-height: 80px;font-weight: normal;display: block;">Ваш пароль: '.$password.'</span>'?>
                    </td>
                </tr>
            </table>
        </td>

</table>
<?php require BASE_PATH.'/templates/emails/default/footer.php';?>
