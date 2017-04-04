<?php require BASE_PATH.'/templates/emails/default/header.php';?>
<table align="center" width="685" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td>
            <table width="685" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td>
                        <span style="font-family: Arial, Helvetica, sans-serif; font-size: 22px; color:#ffffff; line-height: 80px;font-weight: normal;display: block;">Здравствуйте, <?php echo $user->getName();?>!</span>
                        <span style="font-family: Arial, Helvetica, sans-serif; font-size: 22px; color:#ffffff; line-height: 80px;font-weight: normal;display: block;">Вы запросили восстановление пароля на сайте #Sektafood!</span>
                        <span style="font-family: Arial, Helvetica, sans-serif; font-size: 22px; color:#ffffff; line-height: 80px;font-weight: normal;display: block;">Для восстановления пароля перейдите по <a href="<?php echo $url;?>" target="_blank" style="color:#ffffff;">ссылке</a>.</span>
                        <span style="font-family: Arial, Helvetica, sans-serif; font-size: 15px; color:#ffffff; line-height: 80px;font-weight: normal;display: block;"><a href="<?php echo $url;?>" target="_blank" style="color:#ffffff;"><?php echo $url;?></a></span>
                        <span style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color:#ffffff; line-height: 80px;font-weight: normal;display: block;">Ссылка действительна 24 часа.</span>
                    </td>
                </tr>
            </table>
        </td>

</table>
<?php require BASE_PATH.'/templates/emails/default/footer.php';?>
