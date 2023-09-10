<?php


use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

if (!function_exists('supported_langs')) {
    function supported_langs(): Collection
    {
        return collect(config('nova-translation-manager.languages'))->mapWithKeys(function($description, $index){
            if( File::isDirectory(config('nova-translation-manager.lang_path').'/'.$index)){
                return [$index => $description];
            }
        });
    }

}
