<?php require 'header.php'; ?>
<table width="580" class="deviceWidth" border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="#eeeeed" style="margin:0 auto;">
    <tbody>
    <tr>
        <td style="font-size: 13px; color: #959595; font-weight: normal; text-align: left; font-family: Georgia, Times, serif; line-height: 24px; vertical-align: top; padding:10px 8px 10px 8px" bgcolor="#eeeeed">
            <p>Здравствуйте, <?php echo $user['name'];?>!</p>
            <p>Вы запросили восстановление пароля на сайте #SektaFood!</p>
            <p>Для восстановления пароля перейдите по ссылке:</p>
            <p><a href="<?php echo $url;?>" target="_blank"><?php echo $url;?></a> </p>
        </td>
    </tr>
    </tbody></table>
<?php require 'footer.php'; ?>

