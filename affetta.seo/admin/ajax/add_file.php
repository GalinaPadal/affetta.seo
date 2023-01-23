<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");

$APPLICATION->SetTitle("Импорт данных");

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
?>

<?

$current_path = $_FILES['file']['tmp_name'];
$filename = $_FILES['file']['name'];
$new_path = '../file/'.$filename;

if(move_uploaded_file($current_path, $new_path)) {
//    $file=file_get_contents('zip://file.xlsx#xl/sharedStrings.xml');
//    $xml=(array)simplexml_load_string($file);
    $sst=array();
//    foreach ($xml['si'] as $item=>$val)$sst[]=iconv('UTF-8','windows-1251',(string)$val->t);

    $file = simplexml_load_file($new_path);
    $xml=(array)simplexml_load_string($file);

    $data=array();
    foreach ($xml->sheetData->row as $row){
        $currow=array();
        foreach ($row->c as $c){
            $value=(string)$c->v;
            $attrs=$c->attributes();
            if ($attrs['t']=='s'){
                $currow[]=$sst[$value];
            }else{
                $currow[]=$value;
            }
        }
        $data[]=$currow;
    }
    dump($new_path);

}
else{
    echo 'Возникла ошибка при загрузке файла';
}

?>

<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>
