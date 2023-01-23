<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");

$APPLICATION->SetTitle("Добавим sitemap-custom.xml");

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
CModule::IncludeModule("affetta.seo");
AffettaSeoHL::reindexSearch();
?>

<p>Поздравляю, ты - волшебник! Sitemap-custom.xml снова с нами <a href="<?=$_SERVER["HTTP_ORIGIN"];?>/sitemap.xml">ПРОВЕРИТЬ</a></p>

<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>