<?php

namespace App\Http\Controllers;

use Exception;
use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_ClearValuesRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class SyncDataController extends Controller
{
    protected $googleClient = null;
    protected $googleServiceSheets = null;

    protected $spreadsheetId = null;

    public function __construct()
    {
        // Путь к JSON-файлу с ключами сервисного аккаунта
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . config_path() . '/credentials.json');

        $this->googleClient = new Google_Client();
        $this->googleClient->useApplicationDefaultCredentials();
        $this->googleClient->addScope(Google_Service_Sheets::SPREADSHEETS);

        $this->googleServiceSheets = new Google_Service_Sheets($this->googleClient);

        // Получаем $spreadsheetId из ссылки
        $syncLink = Storage::get('sync-link.txt');
        if (!empty($syncLink)) {
            $re = '/https\:\/\/docs\.google\.com\/spreadsheets\/d\/(\S+)\/edit.?/m';
            preg_match_all($re, $syncLink, $matches, PREG_SET_ORDER, 0);
            if (!empty($matches[0][1])) {
                $this->spreadsheetId = $matches[0][1];
            }
        }
    }

    public function clearGoogleSpreadsheet(): JsonResponse
    {
        $sheetName = 'Лист1'; // Имя листа, который нужно очистить
        $range = $sheetName . '!A:F'; // Очистить весь диапазон (A до F)

        try {
            $this->googleServiceSheets->spreadsheets_values->clear(
                $this->spreadsheetId,
                $range,
                new Google_Service_Sheets_ClearValuesRequest()
            );
            return response()->json(['message' => 'SpreadSheet is cleared']);
        } catch (Exception $e) {
            return response()->json(['message' => 'SpreadSheet is don\'t cleared', 'error' => $e->getMessage()], 400);
        }
    }

    public function fetch(int $count = 0): JsonResponse
    {
        $response = Artisan::call('app:sync' . ($count > 0 ? ' ' . $count : ''));

        // Получение статуса выполнения
        $status = Artisan::output();

        // Получение вывода команды
        $output = Artisan::output();

        return response()->json([
            'response' => $response,
            'status' => $status,
            'output' => $output
        ]);
    }

    public function fetchFormat(int $count = 0)
    {
        Artisan::call('app:sync' . ($count > 0 ? ' ' . $count : ''));

        // Получение статуса выполнения
        $output = Artisan::output();

        return response()->make($output, 200, [
            'Content-Type' => 'text/plain'
        ]);
    }
}
