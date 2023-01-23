<?
IncludeModuleLangFile(__FILE__);

use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
use Bitrix\Main\Entity;

Loader::includeModule("highloadblock");

define('SITE_SERVER_NAME_FULL', ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"]);
CModule::AddAutoloadClasses(
    "affetta.seo",
    array(
        "CUserTypeHtml" => "classes/general/cusertypehtml.php",
        "AffettaSeoHL" => "classes/general/affettaseohl.php",
    )
);

/******** Данные для init.php ***********/

//Импорт данных в HL
AddEventHandler("main", "OnAdminContextMenuShow", "AddExportButton");
function AddExportButton(&$items) {
    Loader::includeModule("highloadblock");
    $hlblock_ID = HLBT::getList(array(
        'select' => array('ID'),
        'filter' => array('=NAME' => 'AffettaSeo'),
        'limit' => 1,
    ))->fetch();

    //add custom button to the index page toolbar
    $path=$_SERVER['REQUEST_URI'];
    $path=parse_url($path);
    $path=$path['path'];
    if ($path == '/bitrix/admin/highloadblock_rows_list.php' && $_REQUEST["ENTITY_ID"]==$hlblock_ID["ID"]) {
        $items[1]["TEXT"] = 'Импорт данных';
        $items[1]["ICON"] = 'importSeo';
        $items[1]["LINK"] = '/local/modules/affetta.seo/includes/importSeo.php';

    }
}
//обавляем кастомные ссылки в sitemap.php
$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->addEventHandler('', 'AffettaSeoOnAfterAdd', 'OnAfterAffettaSeoAddUpdateHandler');
$eventManager->addEventHandler('', 'AffettaSeoOnAfterUpdate', 'OnAfterAffettaSeoAddUpdateHandler');
function OnAfterAffettaSeoAddUpdateHandler(\Bitrix\Main\Entity\Event $event)

{
    $arFields = $event->getParameter("fields");
    if($arFields["UF_REDIRECT"]["VALUE"]){
        $file = $_SERVER["CONTEXT_DOCUMENT_ROOT"].'/sitemap-custom.xml';

        if (!file_exists($file)) {
            $fp = fopen($file, "w");
            fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?>'.
                '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');
            fclose($fp);
        }
        else{
            Loader::includeModule("highloadblock");
            $fp = fopen($file, "wt");
            $link = '<?xml version="1.0" encoding="UTF-8"?>'.
                '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

            $hlblock_ID = HLBT::getList(array(
                'select' => array('ID'),
                'filter' => array('=NAME' => 'AffettaSeo'),
                'limit' => 1,
            ))->fetch();
            $hlblock = HLBT::getById($hlblock_ID["ID"])->fetch();

            $entity = HLBT::compileEntity($hlblock);
            $entity_data_class = $entity->getDataClass();

            $rsData = $entity_data_class::getList(array(
                "select" => array("*"),
                "order" => array("ID" => "ASC"),
                "filter" => array("UF_ACTIVE"=> "1", "UF_MAP"=>'1', '!UF_REDIRECT'=>false)
            ));

            while($arData = $rsData->Fetch()){
                $link .= '<url><loc>'.$_SERVER["HTTP_ORIGIN"].$arData["UF_REDIRECT"].'</loc><lastmod>'.date("c").'</lastmod></url>';
            }
            $link.='</urlset>';

            fwrite($fp, $link);
            fclose($fp);
        }
    }
}