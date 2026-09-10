<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$kernel->handle($request);
Illuminate\Support\Facades\Auth::login(App\Models\User::where('role','principal')->first());
echo Illuminate\Support\Facades\Auth::user()->name."\n";
$html = Illuminate\Support\Facades\View::make('principal.layouts.app', ['slot'=>'','title'=>'x','breadcrumb'=>'x'])->render();
$html = str_replace('>', ">\n", $html);
file_put_contents(storage_path('app/principal_out.txt'), $html);
echo "written\n";