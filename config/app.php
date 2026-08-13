<?php
return ['name'=>env('APP_NAME','Naturmarkt'),'env'=>env('APP_ENV','production'),'debug'=>(bool)env('APP_DEBUG',false),'url'=>env('APP_URL','http://localhost'),'timezone'=>'Europe/Berlin','locale'=>env('APP_LOCALE','de'),'fallback_locale'=>env('APP_FALLBACK_LOCALE','de'),'cipher'=>'AES-256-CBC','key'=>env('APP_KEY')];
