<?php require 'header.php'; ?>
<table width="580" class="deviceWidth" border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="#eeeeed" style="margin:0 auto;">
    <tbody>
    <tr>
        <td style="font-size: 13px; color: #959595; font-weight: normal; text-align: left; font-family: Georgia, Times, serif; line-height: 24px; vertical-align: top; padding:10px 8px 10px 8px" bgcolor="#eeeeed">
            <p>Приветствуем, <?php echo $user['name'];?>!</p>
            <p>Вы зарегистрировались на сайте #SektaFood!</p>
            <p>Ваш логин: <?php echo $user['login'];?></p>
            <?php if (isset($user['password']) && $user['password'] != ''){?>
            <p>Ваш пароль: <?php echo $user['password'];?></p>
            <?php }?>
        </td>
    </tr>
    </tbody></table>
<?php require 'footer.php'; ?>

