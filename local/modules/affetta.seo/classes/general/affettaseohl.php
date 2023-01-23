<?

use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
use Bitrix\Main\Entity;

Loader::includeModule("highloadblock");

class AffettaSeoHL
{
    public function SEOUrl($url){
        Loader::includeModule('highloadblock');

        $hlblock_ID = HLBT::getList(array(
            'select' => array('ID'),
            'filter' => array('=NAME' => 'AffettaSeo'),
            'limit' => 1,
        ))->fetch();;

        $hlblock = HLBT::getById($hlblock_ID["ID"])->fetch();

        $entity = HLBT::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();

        $seo = $entity_data_class::getList(array(
            "select" => array("*"),
            "order" => array("ID" => "ASC"),
            "filter" => array("UF_ACTIVE"=> "1", "UF_URL"=>$url)
        ))->Fetch();

        if(!$seo["ID"]){
            $seo = $entity_data_class::getList(array(
                "select" => array("*"),
                "order" => array("ID" => "ASC"),
                "filter" => array("UF_ACTIVE"=> "1", "UF_REDIRECT"=>$url)
            ))->Fetch();
        }

        return $seo;
    }

    public function OnPageStart(){
        global $APPLICATION;
        if(stripos($APPLICATION->GetCurDir(), '/bitrix/') !== false) return;
        $url = $APPLICATION->GetCurDir();
        $seo = AffettaSeoHL::SEOUrl($url);
        if ($seo["UF_REDIRECT"]) {
            if ($_SERVER["REQUEST_URI"] == $seo["UF_URL"]) {
                header('Location: ' . SITE_SERVER_NAME_FULL . $seo["UF_REDIRECT"]);
            }
            AffettaSeoHL::NewUrlSEO($seo["UF_URL"], $seo["UF_REDIRECT"]);
            $GLOBALS['SEO_TEXT_HTML'] = ($seo["UF_BOTTOM_TEXT"] ? $seo["UF_BOTTOM_TEXT"] : ' ');
            $GLOBALS['SEO_TOP_TAGS'] = $seo["UF_TOP_TAGS"];
            $GLOBALS['SEO_BOTTOM_TAGS'] = $seo["UF_BOTTOM_TAGS"];
        }
    }
    public function OnEpilog(){
        global $APPLICATION;
        if(stripos($APPLICATION->GetCurDir(), '/bitrix/') !== false) return;
        $url = $APPLICATION->GetCurDir();
        $seo = AffettaSeoHL::SEOUrl($url);
        if ($seo["ID"]) {
            $APPLICATION->SetTitle($seo["UF_H1"]);
            $APPLICATION->SetPageProperty('title', $seo["UF_TITLE"]);
            $APPLICATION->SetPageProperty("keywords", $seo["UF_KEYWORDS"]);
            $APPLICATION->SetPageProperty("description", $seo["UF_DESCRIPTION"]);
            if ($seo["UF_PATH"]) {
                $APPLICATION->AddChainItem($seo["UF_PATH"], $seo["UF_URL"]);
            }
        }
    }


    public function NewUrlSEO($page, $url_redirect)
    {
        global $APPLICATION;
        $application = \Bitrix\Main\Application::getInstance();
        $context = $application->getContext();
        //$request = $context->getRequest();
        $Response = $context->getResponse();
        $Server = $context->getServer();
        $server_get = $Server->toArray();
        $server_get["REQUEST_URI"] = $page;
        $Server->set($server_get);
        $context->initialize(new Bitrix\Main\HttpRequest($Server, array(), array(), array(), $_COOKIE), $Response, $Server);
        $_SERVER["REQUEST_URI"] = $url_redirect;
        $APPLICATION ->sDocPath2 = GetPagePath(false, true);
        $APPLICATION ->sDirPath = GetDirPath($APPLICATION ->sDocPath2);
    }
    //Добавляем кастомный sitemap для seo ссылок
    function reindexSearch()
    {
        Loader::includeModule('search');

        // добавим свои урлы в sitemap
        $path = $_SERVER['DOCUMENT_ROOT'] . "/sitemap.xml";
        if( strpos(file_get_contents($path),'sitemap-custom.xml') == false) {
            $sitemap = new SimpleXMLElement($path, null, true);// Будем использовать simplexml
            $sitemapItem = $sitemap->addChild('sitemap'); // добавим элемент sitemap
            $sitemapItem->addChild('loc', SITE_SERVER_NAME_FULL . '/sitemap-custom.xml'); // добавим дополнительный файл sitemap
            // взято из ядра битрикса - вычисление отклонения от GMT
            $iTZ = date("Z");
            $iTZHour = intval(abs($iTZ) / 3600);
            $iTZMinutes = intval((abs($iTZ) - $iTZHour * 3600) / 60);
            $strTZ = ($iTZ < 0 ? "-" : "+") . sprintf("%02d:%02d", $iTZHour, $iTZMinutes);
            // добавляем дату модификации
            $sitemapItem->addChild('lastmod', date('Y-m-d') . 'T' . date('H:i:s') . $strTZ);

            // сохраняем файл
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/sitemap.xml", $sitemap->asXML(), LOCK_EX);
        }
        return __METHOD__ . '();';
    }
}?>