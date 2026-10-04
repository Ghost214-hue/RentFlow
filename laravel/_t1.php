<?php
require __DIR__.'/vendor/autoload.php';
$app=require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$r=new App\Mail\RenderMailTemplate();
try{
  $out=$r->render(App\Mail\MailTemplate::PasswordReset,['name'=>'Ada <b>Lovelace</b>','code'=>'482913','expires'=>'15 minutes']);
  echo "subject: ".$out['subject']."\n";
  echo "body length: ".strlen($out['body'])."\n";
  echo "escapes name: ".(str_contains($out['body'],'&lt;b&gt;')?'yes':'NO')."\n";
  echo "has code: ".(str_contains($out['body'],'482913')?'yes':'no')."\n";
}catch(Throwable $e){ echo "ERR: ".get_class($e).": ".$e->getMessage()."\n"; }

try{
  $r->render(App\Mail\MailTemplate::PasswordReset,['name'=>'Ada','expires'=>'15 minutes']);
  echo "MISSING VAR NOT CAUGHT\n";
}catch(App\Mail\MissingMailVariables $e){
  echo "missing-var guard: ".implode(',',$e->missing)."\n";
}

// A template whose view does not exist must fail loudly, not send blank.
try{
  $out=$r->render(App\Mail\MailTemplate::TenantWelcome,['tenant'=>'A','property'=>'P','house'=>'H','email'=>'e','setup_link'=>'x']);
  echo "tenant-welcome rendered: ".strlen($out['body'])." bytes\n";
}catch(Throwable $e){ echo "expected (no view yet): ".get_class($e).": ".substr($e->getMessage(),0,70)."\n"; }