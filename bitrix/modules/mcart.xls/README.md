# README #

http://marketplace.1c-bitrix.ru/solutions/mcart.xls/

## Software requirements ##

* PHP version 5.6 or newer
* PHP extension php_zip enabled
* PHP extension php_xml enabled
* PHP extension php_gd2 enabled (if not compiled in)

## Events ##


### onBeforeImportElement ###

Event parameters:

* array arFields
* array arProfile
* array arDefaultValues
* array arColumnsKeys
* array arColumnsByKeys
* array PhpOffice\PhpSpreadsheet\Cell\Cell   arCells
* int ELEMENT_ID

Result parameters:

* array arFields

Example:

```php
$eventManager = Bitrix\Main\EventManager::getInstance();
$eventManager->addEventHandler('mcart.xls', 'onBeforeImportElement', function (\Bitrix\Main\Event $event){
    $arFields = $event->getParameters()['arFields'];
    $arFields[Mcart\Xls\ORM\Profile\ColumnTable::SAVE_IN_PREF__FIELD]['NAME'] .= ' (test)';
    $result = new \Bitrix\Main\EventResult(1, $arFields);
    return $result;
});
```


### onAfterImportElement ###

Event parameters:

* array arFields
* array arProfile
* array arDefaultValues
* array arColumnsKeys
* array arColumnsByKeys
* array PhpOffice\PhpSpreadsheet\Cell\Cell arCells
* int ELEMENT_ID


### onAfterImportStep ###

Event parameters:

* array arProfile
* array arDefaultValues
* array arColumnsKeys
* array arColumnsByKeys
* array addedElementIds
* array updatedElementIds
* int processedRows


### onCompleteImport ###

Event parameters:

* array arProfile
* array arDefaultValues
* array arColumnsKeys
* array arColumnsByKeys
* int processedRows

