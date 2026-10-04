<?php
require __DIR__.'/vendor/autoload.php';
$app=require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Mail\MailTemplate; use App\Mail\RenderMailTemplate;
$r=new RenderMailTemplate();
$sample=[
 'tenant'=>'Ada Lovelace','name'=>'Ada','property'=>'Sunrise Court','house'=>'A1',
 'email'=>'ada@example.test','setup_link'=>'https://app.test/setup?t=abc','code'=>'482913',
 'expires'=>'15 minutes','amount'=>'50,000.00','category'=>'Rent','date'=>'2026-02-01',
 'balance'=>'12,500.00','month'=>'February 2026','payment_instructions'=>"M-Pesa 0712345678\nPaybill 522533",
 'id'=>'TC-1042','link'=>'https://app.test/complaints/1','title'=>'Water outage',
 'description'=>'Supply will be interrupted on Tuesday.','sender_name'=>'Manager',
 'tenant_name'=>'Ada Lovelace','owner_name'=>'Grace','house'=>'A1','reason'=>'Relocating',
 'national_id'=>'KE-998877','invoice_section'=>"February rent - KES 50,000.00",
];
$bad=0;
foreach(MailTemplate::cases() as $t){
  $vars=$sample;
  foreach($t->requiredVariables() as $v){ $vars[$v] ??= 'X'; }
  try{
    $out=$r->render($t,$vars);
    $len=strlen($out['body']);
    $hasUnresolved=substr_count($out['body'],'{{')>0 || substr_count($out['subject'],'{{')>0;
    printf("%-22s %-52s %6d bytes%s\n",$t->value,substr($out['subject'],0,50),$len,$hasUnresolved?'  <-- UNRESOLVED PLACEHOLDER':'');
    if($hasUnresolved)$bad++;
  }catch(Throwable $e){ printf("%-22s FAILED: %s\n",$t->value,substr($e->getMessage(),0,80)); $bad++; }
}
echo $bad===0 ? "\nall 13 templates render cleanly\n" : "\n$bad problem(s)\n";