<?php

$infusionsoft_host = 'oq171.infusionsoft.com';
$infusionsoft_api_key = '18d7cdbd3effc301b276229927d0ceba61f327e8ce6b0d9181ebd041f80b822d';

//To Add Custom Fields, use the addCustomField method like below.
//Infusionsoft_Contact::addCustomField('_LeadScore');

//Below is just some magic...  Unless you are going to be communicating with more than one APP at the SAME TIME.  You can ignore it.
Infusionsoft_AppPool::addApp(new Infusionsoft_App($infusionsoft_host, $infusionsoft_api_key, 443));