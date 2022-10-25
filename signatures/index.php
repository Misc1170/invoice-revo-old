<?php
require_once 'protect.php';

header('Content-Type: text/html; charset=UTF-8');
require_once 'lib.php';
$db = db_open('contacts.db');
?>
    <html>
    <head>
        <style>
            .button {
                padding: 5px;
            }
            * {font-family: calibri;}
            .contacts td { padding: 3px; border: 1px solid gray; }
            .contacts { border-collapse: collapse;  }
            .contacts .text { width: 100%; border: 1px solid lightgray; padding: 3px; margin: 3px 0; }
            .templates a { display: block; text-decoration: none; }
            th {font-weight: normal}
            .obzor { background-image: url('images/obzor.png'); width: 67px; height: 21px; position: absolute; bottom: 5px; left: 10px; cursor: pointer; opeacity: 0.7; overflow: hidden; }
            .obzor input {opacity: 0; position: absolute; top: 0px; left: 0px; cursor: pointer; width: 100%; height: 100%;}
        </style>
        <title>Менеджер подписей</title>
    </head>
    <body style="background-image: url('/images/bg_v.jpg')">
    <table width="100%">
        <tr>
            <td width="40%" valign="top" style="padding-right: 5px;">
                <div style="border-bottom: 2px solid #000D27; font-weight: bold; color: #05F;">Управление подписями менеджеров <?=$_SERVER['HTTP_HOST']?></div>
                <h3>Баннер <span style="color: gray; font-weight: normal;">(960x146 JPG)</span>  <?=( checkBanner() ? '<span style="color: green; font-weight: normal">активен</span>' : '<span style="color: red; font-weight: normal">неактивен</span>')?></h3>
                <a href="<?=file_get_contents('banner_link')?>"><img src="banner.jpg?v=<?=time()?>" alt="картинка не найдена" style="max-width: 100%; max-height: 146px; min-width: 100%;"></a><br><br>
                <form method="POST" enctype="multipart/form-data" action="action_banner.php">
                    <input type="file" name="banner" class="button">
                    <table>
                        <tr>
                            <td>
                                Показывать баннер до: <input type="text" name="banner_time" value="<?=file_get_contents('banner_time')?>" class="button" placeholder="напр. 10.05.2017" style="width:100px"> (<i>dd.mm.YYYY</i>)
                                <br>Ссылка баннера: <input type="text" name="banner_link" value="<?=file_get_contents('banner_link')?>" class="button" placeholder="https://www.f...." style="width:200px"> [[banner_link]]
                                <br>Alt-текст баннера [[banner_text]]<br><textarea placeholder="Текстовая версия баннера" name="banner_text" style="width: 500px; height: 45px;"><?=file_get_contents('banner_text')?></textarea>
                            </td>
                            <td>
                                <input type="submit" value="Сохранить" style="background-color: #95DC9E; border: 1px solid #ADADAD; cursor: pointer;" class="button">
                            </td>
                        </tr>
                    </table>

                    <br><i style="color: gray;">Дата вида 19.05.2017. Если дата прошла или пусто - баннер не будет показан</i>
                    <!--<input type="submit" value="Удалить" style="background-color: #DC9595; border: 1px solid #ADADAD;" class="button">-->
                </form>

                <h3>Шаблон подписи</h3>
                <form action="action_templates.php" method="POST" style="margin-bottom: 0;">
                    <table class="templates">
                        <tr>
                            <td align="center">
                                <a href="template_main.html" target="_blank">основной</a>
                                <textarea name="template_main"><?=@file_get_contents('template_main.html')?></textarea>
                            </td>
                            <td align="center">
                                <a href="template_banner.html" target="_blank">баннер</a>
                                <textarea name="template_banner"><?=@file_get_contents('template_banner.html')?></textarea>
                            </td>
                            <td align="center" href="template_en.html" target="_blank">
                                <a href="template_en.html" target="_blank">EN</a>
                                <textarea name="template_en"><?=@file_get_contents('template_en.html')?></textarea>
                            </td>
                            <td valign="bottom">
                                <input type="submit" value="СОХРАНИТЬ" class="button">
                            </td>
                        </tr>
                    </table>
                </form>
                <i style="color: gray;">Плейсхолдеры: [[fio]],[[phone]], ...</i>
                <h3>Инструкция</h3>
                Подпись для менеджера доступна по следующим URL:<br>
                https://<?=$_SERVER['HTTPS_HOST']?>/signatures/getSignature.php?email=[email]&type=[type]
                <br>Где [mail] - Mail менеджера
                <br>Где [type] - Тип подписи, например: Main, banner, en
                <br>Если не указать тип, будет взята подпись с баннером, если он актуален и без него если нет.
            </td>
            <td valign="top">
                <h3>Контакты</h3>
                <table border="0" class="contacts">
                    <thead>
                    <tr>
                        <th><b>Картинка</b><br>83x110 JPG<br>img</th>
                        <th><b>ФИО</b><br>fio, fio_en</th>
                        <th><b>Email<span style="color: red; font-weight: bold;">*</span></b><br>email</th>
                        <th><b>Должность</b><br>doljnost, doljnost_en</th>
                        <th width="255"><b>Телефон</b><br>phone, phone_en</th>
                        <th>Подписи:</th>
                        <th></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $contacts = getContacts($db);

                    if(is_array($contacts)) {
                        foreach ($contacts as $val) {
                            echo '
							<tr bgcolor="white">
								<form method="post" action="action_contacts.php" enctype="multipart/form-data">
									<td style="position: relative;">
										<img src="images/' . current(explode('@', $val['email'])) . '.jpg' . '" width="83" height="110">
										<div class="obzor"><input type="file" name="photo"></div>
									</td>
									<td>
										<input type="text" name="fio" value="' . $val['fio'] . '" class="text"><br>
										<input type="text" name="fio_en" value="' . $val['fio_en'] . '" class="text">
									</td>
									<td><input type="text" name="email" value="' . $val['email'] . '" class="text"></td>
									<td>
										<input type="text" name="doljnost" value="' . $val['doljnost'] . '" class="text"><br>
										<input type="text" name="doljnost_en" value="' . $val['doljnost_en'] . '" class="text">
									</td>
									<td>
										<input type="text" name="phone" value="' . $val['phone'] . '" class="text" style="width: 250px">
										<input type="text" name="phone_en" value="' . $val['phone_en'] . '" class="text" style="width: 250px">
									</td>
									<td align="center">
                                        <a href="getSignature.php?email='.$val['email'].'&type=main">main</a><br>
                                        <a href="getSignature.php?email='.$val['email'].'&type=banner">banner</a><br>
                                        <a href="getSignature.php?email='.$val['email'].'&type=en">en</a>
                                    </td>
									<td align="center">
										<a href="action_contacts.php?delete_contact=' . $val['id'] . '">
											<img src="images/edit-delete_9347.png" alt="">
										</a>
									</td>
									<td align="center">
										<input type="hidden" name="contact_id" value="' . $val['id'] . '">
										<input type="submit" name="save_contact" value="Сохранить" style="width: 32px; height: 32px; border: none; cursor: pointer; background-image: url(images/save_32_6505.png); font-size: 0">
									</td>
								</form>
							</tr>';
                        }
                    }

                    ?>
                    <tr bgcolor="white">
                        <form method="post" action="action_contacts.php" enctype="multipart/form-data">
                            <td style="position: relative;">
                                <div class="obzor"><input type="file" name="photo"></div>
                            </td>
                            <td>
                                <input type="text" name="fio" placeholder="ФИО" class="text"><br>
                                <input type="text" name="fio_en" placeholder="ФИО EN" class="text">
                            </td>
                            <td><input type="text" name="email" placeholder="email" class="text"></td>
                            <td>
                                <input type="text" name="doljnost" placeholder="Должность" class="text"><br>
                                <input type="text" name="doljnost_en" placeholder="Должность EN" class="text">
                            </td>
                            <td>
                                <input type="text" name="phone" placeholder="Тел." class="text" style="width: 250px">
                                <input type="text" name="phone_en" placeholder="Тел. EN" class="text" style="width: 250px">
                            </td>
                            <td align="center" colspan="2">
                                <input type="submit" name="add_contact" value="Добавить"  class="button">
                            </td>
                    </tr>
                    </form>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
    </body>
    </html>

<?php
function checkBanner(){
    $dateString = file_get_contents('banner_time');

    if(empty($dateString))
        return false;

    $dateDigital = strtotime($dateString);

    if(time() > $dateDigital)
        return false;

    return true;
}
?>