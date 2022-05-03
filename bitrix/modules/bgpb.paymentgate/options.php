<?$module_id = 'bgpb.paymentgate';

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

$modRights = $GLOBALS['APPLICATION']->GetGroupRight($module_id);

if (!$GLOBALS['USER']->isAdmin() || $modRights<'R')
    $GLOBALS['APPLICATION']->authForm('Nope');

$context = Application::getInstance()->getContext();
$request = $context->getRequest();

Loc::loadMessages($context->getServer()->getDocumentRoot().'/bitrix/modules/main/options.php');
Loc::loadMessages(__FILE__);

$tabControl = new CAdminTabControl('tabControl', array(
    array('DIV' => 'edit1', 'TAB' => Loc::getMessage('IM_PG_OPTIONS_TAB1_TITLE'), 'TITLE' => Loc::getMessage('IM_PG_OPTIONS_TAB1_TITLE')),
    array('DIV' => 'edit2', 'TAB' => Loc::getMessage('IM_PG_OPTIONS_TAB2_TITLE'), 'TITLE' => Loc::getMessage('IM_PG_OPTIONS_TAB2_TITLE')),
    array('DIV' => 'edit3', 'TAB' => Loc::getMessage('MAIN_TAB_RIGHTS'), 'ICON' => 'main_settings', 'TITLE' => Loc::getMessage('MAIN_TAB_RIGHTS')),
));

$sslCsrFields = array(
    'commonName'=>'ssl-common-name',
    'organizationName'=>'ssl-organization',
    'organizationalUnitName'=>'ssl-organization-unit',
    'localityName'=>'ssl-city-locality',
    'stateOrProvinceName'=>'ssl-state-province',
    'countryName'=>'ssl-country-region'
);
$csrSettings = array(
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
    'encrypt_key' => false,
    'encrypt_key_cipher' => OPENSSL_CIPHER_3DES
);

if($request->getPost('ssl-key-gen')!=''){
    if($request->getPost('ssl-password')==''){
        $message = new CAdminMessage(Loc::getMessage('IM_PG_OPTIONS_SSL_KEY_PASSW_ERROR'));
        echo $message->Show();
    }else{
        $fileName = 'key_'.time().'.key';
        $privKey = openssl_pkey_new($csrSettings);
        if(openssl_pkey_export_to_file($privKey,$context->getServer()->getDocumentRoot().'/'.$fileName,$request->getPost('ssl-password'), $csrSettings))
            $message = new CAdminMessage(
                array(
                    'MESSAGE'=>Loc::getMessage('IM_PG_OPTIONS_SSL_PR_KEY_RESULT_SUCCESS',array('#FILE_NAME#'=>$fileName)),
                    'TYPE'=>'OK',
                    'HTML'=>true
                )
            );
        else
            $message = new CAdminMessage(Loc::getMessage('IM_PG_OPTIONS_SSL_PR_KEY_RESULT_ERROR'));

        echo $message->Show();
    }
}elseif($request->getPost('ssl-certificate-order')!=''){
    $errors = array();

    if(empty($_FILES['ssl-key-file']['size']))
        $errors[] = Loc::getMessage('IM_PG_OPTIONS_SSL_CERTIF_KEY_FILE_ERROR');
    if($request->getPost('ssl-key-password')=='')
        $errors[] = Loc::getMessage('IM_PG_OPTIONS_SSL_CERTIF_KEY_PASSWORD_ERROR');
    if($request->getPost('ssl-mailto')==''){
        $errors[] = Loc::getMessage('IM_PG_OPTIONS_SSL_CERTIF_EMAIL_ERROR');
    }else{
        $val = $request->getPost('ssl-mailto');
        if(!filter_var($val, FILTER_VALIDATE_EMAIL))
            $errors[] = Loc::getMessage('IM_PG_OPTIONS_SSL_CERTIF_EMAIL_ERROR');
    }

    foreach($sslCsrFields as $field){
        if($request->getPost($field)=='') {
            $errors[] = Loc::getMessage('IM_PG_OPTIONS_SSL_CERTIF_REQUIRED_FIELD', array('#FIELD#' => Loc::getMessage(strtoupper($field))));
        }elseif($field=='ssl-country-region'){
            $val = $request->getPost($field);
            if(strlen($val)!=2)
                $errors[] = Loc::getMessage('IM_PG_OPTIONS_SSL_CERTIF_COUNTRY_FIELD_LENGTH');
        }
    }

    if(empty($errors)){
        $dn = array();
        foreach($sslCsrFields as $key=>$field)
            $dn[$key] = $request->getPost($field);
        $privKey = openssl_pkey_get_private('file://'.$_FILES['ssl-key-file']['tmp_name'],$request->getPost('ssl-key-password'));
        $csr = openssl_csr_new($dn, $privKey);
        openssl_csr_export($csr, $strCsr);

        $fileName = preg_replace("/\.[^.]+$/", "", $_FILES['ssl-key-file']['name']).'_scr';

        $headers = 'From: '.Option::get('main', 'email_from');
        // boundary
        $semi_rand = md5(time());
        $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
        // headers for attachment
        $headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";
        // message text
        $contents = Loc::getMessage('IM_PG_OPTIONS_MAIL_BODY');
        // multipart boundary
        $message = "--{$mime_boundary}\n" . "Content-Type: text/plain; charset=\"uft-8\"\n" .
            "Content-Transfer-Encoding: 7bit\n\n" . $contents. "\n\n";
        // preparing attachments
        $message .= "--{$mime_boundary}\n";

        $message .= "Content-Type: application/octet-stream; name=\"".$fileName."\"\n" .
            "Content-Description: ".$fileName."\n" .
            "Content-Disposition: attachment;\n" . " filename=\"".$fileName."\"; size=".strlen($strCsr).";\n" .
            "Content-Transfer-Encoding: base64\n\n" . chunk_split(base64_encode($strCsr)) . "\n\n";
        $message .= "--{$mime_boundary}--";
        if (mail($request->getPost('ssl-mailto'), Loc::getMessage('IM_PG_OPTIONS_MAIL_TITLE'), $message, $headers)){
            CAdminMessage::ShowNote(Loc::getMessage('IM_PG_OPTIONS_MAIL_SUCCESS'));
        } else {
            $message = new CAdminMessage(Loc::getMessage('IM_PG_OPTIONS_MAIL_ERROR'));
            echo $message->Show();
        }
    }else{
        $message = new CAdminMessage(implode('<br>',$errors));
        echo $message->Show();
    }
}

?><form method="post" enctype="multipart/form-data" action="<?=sprintf('%s?mid=%s&lang=%s', $request->getRequestedPage(), urlencode($mid), LANGUAGE_ID)?>"><?
    echo bitrix_sessid_post();
    $tabControl->begin();
    $tabControl->beginNextTab();
        ?><tr class="heading">
            <td colspan="2"><b><?=Loc::getMessage('IM_PG_OPTIONS_SSL_PR_KEY_TITLE')?></b></td>
        </tr>
        <tr>
            <td width="50%" class="adm-detail-content-cell-l"><?=Loc::getMessage('IM_PG_OPTIONS_SSL_PR_KEY_PASSWORD')?></td>
            <td width="50%" class="adm-detail-content-cell-r">
                <input type="password" name="ssl-password" value="<?=$request->getPost('ssl-password')?>">
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center"><input type="submit" name="ssl-key-gen" value="<?=Loc::getMessage('IM_PG_OPTIONS_SSL_PR_KEY_SUBMIT')?>"></td>
        </tr><?
    $tabControl->beginNextTab();
    ?><tr class="heading">
        <td colspan="2"><b><?=Loc::getMessage('IM_PG_OPTIONS_SSL_CERTIF_TITLE')?></b></td>
    </tr><?
    ?><tr>
        <td width="50%" class="adm-detail-content-cell-l"><?=Loc::getMessage('IM_PG_OPTIONS_SSL_CERTIF_KEY_FILE')?></td>
        <td width="50%" class="adm-detail-content-cell-r">
            <input type="file" name="ssl-key-file">
        </td>
    </tr>
    <tr>
        <td width="50%" class="adm-detail-content-cell-l"><?=Loc::getMessage('IM_PG_OPTIONS_SSL_CERTIF_KEY_PASSWORD')?></td>
        <td width="50%" class="adm-detail-content-cell-r">
            <input type="password" name="ssl-key-password" value="<?=$request->getPost('ssl-key-password')?>">
        </td>
    </tr><?
    foreach($sslCsrFields as $field):
        ?><tr>
            <td width="50%" class="adm-detail-content-cell-l"><?=Loc::getMessage(strtoupper($field))?>*</td>
            <td width="50%" class="adm-detail-content-cell-r">
                <input type="text" name="<?=$field?>" value="<?=$field=='ssl-common-name'&&$request->getPost($field)==''?$context->getServer()->getHttpHost():$request->getPost($field)?>">
            </td>
        </tr><?
    endforeach;
    ?><tr>
        <td width="50%" class="adm-detail-content-cell-l"><?=Loc::getMessage('IM_PG_OPTIONS_SSL_CERTIF_EMAIL_ADDRESS')?></td>
        <td width="50%" class="adm-detail-content-cell-r">
            <input type="email" name="ssl-mailto" value="<?=$request->getPost('ssl-mailto')==''?'support@hutkigrosh.by':$request->getPost('ssl-mailto')?>">
        </td>
    </tr>
    <tr>
        <td colspan="2" align="center"><input type="submit" name="ssl-certificate-order" value="<?=Loc::getMessage('IM_PG_OPTIONS_SSL_CERTIF_SUBMIT')?>"></td>
    </tr><?

    $tabControl->beginNextTab();
    require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/admin/group_rights.php');
    $tabControl->buttons();
    ?><input type="submit" name="Update" <?if ($modRights<'W') echo 'disabled' ?> value="<?= Loc::getMessage('MAIN_SAVE')?>">
    <input type="reset" name="reset" value="<?echo Loc::getMessage('MAIN_RESET')?>">
    <input type="hidden" name="Update" value="Y">
    <?$tabControl->end();?>
</form><?
