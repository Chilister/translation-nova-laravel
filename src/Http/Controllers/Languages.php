<?php

namespace Chilister\Translation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Chilister\Translation\Services\Data;

class Languages extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function all(Request $request): JsonResponse
    {
        return response()->json(Data::languages());
    }


    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'index' => 'required|string|size:2',
            'description' => 'required|string|max:50',
        ]);


        if ($validation->fails()) {
            return response()->json([
                'response' => false,
                'message' => "Inputs cannot be empty. Index must 2 symbols, description maximum 50",
            ], 422);
        }


        $data = Data::languages();
        $index = $request->input('index');
        $description = $request->input('description');


        if (!isset($data[$index])) {

            $data[$index] = $description;

            $configContent = "<?php\n\treturn [\n\n\t'default_locale' => config('app.locale','az'),\n\n\t'lang_path' => env('SITE_LANG_PATH',lang_path().'/'),\n\n\t'disabled_groups' => ['validation','auth','pagination','passwords'  ],\n\n\t'languages' =>";

            $configContent .= Data::replaceToPureContent($data) . "\n\n\t];";

            $fopen = fopen(config_path('nova-translation-manager.php'), 'w+');
            fputs($fopen, $configContent);
            fclose($fopen);

            File::copyDirectory(config('nova-translation-manager.lang_path') .'/'. config('nova-translation-manager.default_locale'), config('nova-translation-manager.lang_path') . $index);

        } else {
            return response()->json([
                'response' => false,
                'message' => 'Language index already exists',
            ], 422);
        }


        return response()->json([
            'response' => true,
            'message' => '',
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'index' => 'required|string|size:2',
            'old_index' => 'required|string|size:2',
            'description' => 'required|string|max:50',
        ]);


        if ($validation->fails()) {
            return response()->json([
                'response' => false,
                'message' => "Inputs cannot be empty. Index must 2 symbols, description maximum 50",
            ], 422);
        }


        $data = Data::languages();
        $index = $request->input('index');
        $old_index = $request->input('old_index');
        $description = $request->input('description');


        if ($old_index!==$index && !isset($data[$index])) {

            File::copyDirectory(config('nova-translation-manager.lang_path') .'/'. $old_index, config('nova-translation-manager.lang_path') .'/'. $old_index.'_old');
            File::move(config('nova-translation-manager.lang_path') .'/'. $old_index, config('nova-translation-manager.lang_path') .'/'. $index );

        }elseif($old_index!==$index && isset($data[$index])) {
            return response()->json([
                'response' => false,
                'message' => 'Language index already exists',
            ], 422);
        }

        unset($data[$old_index]);
        $data[$index] = $description;

        $configContent = "<?php\n\treturn [\n\n\t'default_locale' => config('app.locale','az'),\n\n\t'lang_path' => env('SITE_LANG_PATH',lang_path().'/'), \n\n\t'disabled_groups' => ['validation','auth','pagination','passwords'  ],\n\n\t'languages' =>";

        $configContent .= Data::replaceToPureContent($data) . "\n\n\t];";

        $fopen = fopen(config_path('nova-translation-manager.php'), 'w+');
        fputs($fopen, $configContent);
        fclose($fopen);

        return response()->json([
            'response' => true,
            'message' => '',
        ]);
    }


    public function delete(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'index' => 'required|string|max:3',
        ]);

        $index = $request->input('index');

        if ($validation->fails()) {

            return response()->json([
                'response' => false,
                'message' => 'Error index name',
            ], 422);
        }

        $languages = Data::languages();
        if (!isset($languages[$index])) {
            return response()->json([
                'response' => false,
                'message' => 'Language path not found',
            ], 422);
        }

        try {

            File::deleteDirectory(config('nova-translation-manager.lang_path') . $index . '_old');
            File::move(config('nova-translation-manager.lang_path') . '/'.$index, config('nova-translation-manager.lang_path') .'/'. $index . '_old');

            return response()->json([
                'response' => true,
                'path' => config('nova-translation-manager.lang_path') . $index,
                'message' => 'Successfully deleted',
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'response' => true,
                'message' => 'Error !' . $exception,
            ], 422);
        }
    }
}
