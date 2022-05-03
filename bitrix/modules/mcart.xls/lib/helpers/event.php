<?php

namespace Mcart\Xls\Helpers;

use Bitrix\Main\Event as BxEvent;
use Bitrix\Main\EventResult;

class Event extends BxEvent {
    private $hasErrors;

    public function hasErrors(){
        if($this->hasErrors !== null){
            return $this->hasErrors;
        }
		$this->hasErrors = false;
        if ($this->getResults() === null) {
            return $this->hasErrors;
        }
		/** @var $evenResult EventResult */
		foreach($this->getResults() as $evenResult)	{
			if($evenResult->getType() === EventResult::ERROR){
				$this->hasErrors = true;
				return $this->hasErrors;
			}
		}
		return $this->hasErrors;
	}

    /**
     * @param array $arData
     * @return array|false
     */
    public function mergeFields(array $arData, $breakIfError = true) {
        if ($this->getResults() === null) {
            return $arData;
        }
        /** @var $evenResult EventResult */
        $this->hasErrors = false;
        foreach ($this->getResults() as $eventResult){
            if($eventResult->getType() == EventResult::ERROR){
                $this->hasErrors = true;
                if($breakIfError){
                    return false;
                }
            }
            $arData = array_merge($arData, $eventResult->getParameters());
        }
        return $arData;
    }

}
