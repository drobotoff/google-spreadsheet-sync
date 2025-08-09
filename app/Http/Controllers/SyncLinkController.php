<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SyncLinkController extends Controller
{
    /**
     * Файл в котором храниться ссылка на таблицу
     */
    const SYNC_LINK = 'sync-link.txt';

    /**
     * Сохранить урл гугл таблицы
     * @param Request $request
     * @return JsonResponse
     */
    public function setSyncLink(Request $request): JsonResponse
    {
        $syncLink = $request->get('sync-link', '');

        if (empty($syncLink)/* and Storage::exists(self::SYNC_LINK)*/) {
            Storage::delete(self::SYNC_LINK);
            return response()->json(['sync-link' => $syncLink, 'message' => 'Sync link deleted']);
        } else {
            Storage::put('sync-link.txt', $syncLink);
            return response()->json(['sync-link' => $syncLink, 'message' => 'Sync link saved']);
        }
    }

    /**
     * Получить ссылку на гугл таблицу
     * @return JsonResponse
     */
    public function getSyncLink()
    {
        if (Storage::exists(self::SYNC_LINK)) {
            $syncLink = Storage::get('sync-link.txt');
            return response()->json(['sync-link' => $syncLink, 'message' => 'Sync link get succesfuly']);
        }

        return response()->json(['sync-link' => '', 'message' => 'Sync link don\t set'], 404);
    }
}
