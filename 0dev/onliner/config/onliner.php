<?php

define('ONLINER_TOKEN_URL','https://b2bapi.onliner.by/oauth/token');
define('ONLINER_API_URL','https://cart.api.onliner.by');

define('STATUS_ONLINER_GET','new');  //new  confirmed processing
define('STATUS_ONLINER_SET_STEP1','processing');
define('STATUS_ONLINER_SET_STEP2','confirmed');
define('STATUS_ONLINER_UNSUCCESSFUL','shop_canceled');
define('STATUS_ONLINER_SUCCESSFUL','delivered');

define('NUMBER_LAST_ORDER_FOR_CHECK', 1);