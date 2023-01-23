<?
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

global $APPLICATION;

if(!check_bitrix_sessid()){
    return;
}

if($errorException = $APPLICATION->GetException())
{
    echo(CAdminMessage::ShowMessage($errorException->GetString()));
}
else
{
    echo(CAdminMessage::ShowNote(Loc::getMessage("AFFETTA_SEO_STEP")));
}
?>

<form action="<? echo($APPLICATION->GetCurPage()); ?>">
    <input type="hidden" name="lang" value="<? echo(LANG); ?>" />
    <input type="submit" value="<? echo(Loc::getMessage("AFFETTA_SEO_SUBMIT_BACK")); ?>">
</form>