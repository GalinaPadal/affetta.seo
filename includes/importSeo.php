<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");

$APPLICATION->SetTitle("Импорт данных");

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");?>

<form action="../admin/ajax/add_file.php" method="post" enctype="multipart/form-data">
    <input id="file" type="file" name="file" placeholder="Загрузите файл импорта" >
    <button type="submit">Загрузить</button>
</form>

<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>
