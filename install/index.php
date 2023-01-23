<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

if(class_exists("affetta_seo")) return;

Class affetta_seo extends CModule
{

    public function __construct()
    {
        if(file_exists(__DIR__."/version.php")) {
            $arModuleVersion = array();

            include_once(__DIR__."/version.php");

            $this->MODULE_ID = "affetta.seo";
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
            $this->MODULE_NAME = Loc::GetMessage("AFFETTA_SEO_MODULE_NAME");
            $this->MODULE_DESCRIPTION = Loc::GetMessage("AFFETTA_SEO_MODULE_DESC");

            $this->PARTNER_NAME = Loc::GetMessage("AFFETTA_SEO_PARTNER_NAME");
            $this->PARTNER_URI = Loc::GetMessage("AFFETTA_SEO_PARTNER_URI");
            $this->NEED_MODULES = array();
        }
        return false;
    }

    public function addUserField($id, $field_name, $name, $mandatory, $type_id = "string")
    {
        if($type_id == 'boolean'){
            $default_value = true;
        }

        $oUserTypeEntity = new CUserTypeEntity();

        $aUserFields = array(
            'ENTITY_ID'         => 'HLBLOCK_'.$id,
            'FIELD_NAME'        => $field_name,
            'USER_TYPE_ID'      => $type_id,
            'XML_ID'            => '',
            'SORT'              => 100,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => $mandatory,
            'SHOW_FILTER'       => 'S',
            'SHOW_IN_LIST'      => '',
            'EDIT_IN_LIST'      => '',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          => array(
                'DEFAULT_VALUE' => $default_value,
                'SIZE'          => '100',
                'ROWS'          => '1',
                'MIN_LENGTH'    => '0',
                'MAX_LENGTH'    => '0',
                'REGEXP'        => '',
            ),
            'EDIT_FORM_LABEL'   => array(
                'ru'    => $name,
                'en'    => '',
            ),
            'LIST_COLUMN_LABEL' => array(
                'ru'    => $name,
                'en'    => '',
            ),
            'LIST_FILTER_LABEL' => array(
                'ru'    => $name,
                'en'    => '',
            ),
            'ERROR_MESSAGE' => array(
                'ru'    => '',
                'en'    => '',
            ),
            'HELP_MESSAGE' => array(
                'ru'    => '',
                'en'    => '',
            ),
        );
        $iUserFieldId = $oUserTypeEntity->Add( $aUserFields );
    }

    // add HL
    function InstallHL()
    {
        global $APPLICATION;

        Loader::includeModule('highloadblock');

        $hlblock_ID = Bitrix\Highloadblock\HighloadBlockTable::getList(array(
            'select' => array('ID'),
            'filter' => array('=NAME' => 'AffettaSeo'),
            'limit' => 1,
        ))->fetch();

        if(empty($hlblock_ID['ID']))
        {
            $result = Bitrix\Highloadblock\HighloadBlockTable::add(array(
                'NAME' => 'AffettaSeo',
                'TABLE_NAME' => 'affetta_seo',
            ));

            if (!$result->isSuccess())
            {
                $APPLICATION->ThrowException(
                    Loc::getMessage("AFFETTA_SEO_INSTALL_ERROR_ADD")
                );
            }
            else
            {
                $id = $result->getId();
                $this->addUserField($id, 'UF_ACTIVE', 'Активность', 'N', 'boolean');
                $this->addUserField($id, 'UF_XML_ID', 'Внешний код', 'N');
                $this->addUserField($id, 'UF_H1', 'H1', 'Y');
                $this->addUserField($id, 'UF_URL', 'URL', 'Y');
                $this->addUserField($id, 'UF_REDIRECT', 'Редирект ','N');
                $this->addUserField($id, 'UF_TITLE', 'Title','N');
                $this->addUserField($id, 'UF_DESCRIPTION', 'Description','N');
                $this->addUserField($id, 'UF_KEYWORDS', 'Keywords','N');
                $this->addUserField($id, 'UF_PATH', 'Пункт в навигационной цепочке','N');
                $this->addUserField($id, 'UF_BOTTOM_TEXT', 'Seo текст','N','customhtml');
                $this->addUserField($id, 'UF_TOP_TAGS', 'Верхнее тегирование','N','customhtml');
                $this->addUserField($id, 'UF_BOTTOM_TAGS', 'Нижнее тегирование','N','customhtml');
                $this->addUserField($id, 'UF_GROUP', 'Группа для фильтрации','Y');
                $this->addUserField($id, 'UF_MAP', 'Добавить в sitemap', 'N', 'boolean');
            }
            return true;
        }
        else
        {
            $APPLICATION->ThrowException(
                Loc::getMessage("AFFETTA_SEO_INSTALL_ERROR_EXIST")
            );
        }

    }

    // delete HL
    function UnInstallHL()
    {
        Loader::includeModule('highloadblock');

        $hlblock_ID = Bitrix\Highloadblock\HighloadBlockTable::getList(array(
            'select' => array('ID'),
            'filter' => array('=NAME' => 'AffettaSeo'),
            'limit' => 1,
        ))->fetch();

        if($hlblock_ID['ID'])
        {
            Bitrix\Highloadblock\HighloadBlockTable::delete($hlblock_ID['ID']);
        }
    }

    // add module
    public function DoInstall()
    {
        global $APPLICATION;

        if(CheckVersion(ModuleManager::getVersion("main"), "14.00.00"))
        {
            RegisterModule($this->MODULE_ID);
            RegisterModuleDependences("main", "OnUserTypeBuildList", $this->MODULE_ID, "CUserTypeHtml", "GetUserTypeDescription");
            RegisterModuleDependences("main", "OnPageStart", $this->MODULE_ID, "AffettaSeoHL", "OnPageStart");
            RegisterModuleDependences("main", "OnEpilog", $this->MODULE_ID, "AffettaSeoHL", "OnEpilog");

            $this->InstallHL();
        }
        else
        {
            $APPLICATION->ThrowException(
                Loc::getMessage("AFFETTA_SEO_INSTALL_ERROR_VERSION")
            );
        }


        $APPLICATION->IncludeAdminFile(
            Loc::getMessage("AFFETTA_SEO_INSTALL_TITLE")." \"".Loc::getMessage("AFFETTA_SEO_MODULE_NAME")."\"",
            __DIR__."/step.php"
        );

        return false;
    }

    // delete module
    public function DoUninstall()
    {
        global $APPLICATION;

        $this->UnInstallHL();
        UnRegisterModuleDependences("main", "OnUserTypeBuildList", $this->MODULE_ID, "CUserTypeHtml", "GetUserTypeDescription");

        unRegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage("AFFETTA_SEO_UNINSTALL_TITLE")." \"".Loc::getMessage("AFFETTA_SEO_MODULE_NAME")."\"",
            __DIR__."/unstep.php"
        );

        return false;
    }

}
?>