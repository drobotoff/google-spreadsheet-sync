<?php

namespace App\Service;

use Google\Service\Exception;
use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_BatchUpdateSpreadsheetRequest;
use Google_Service_Sheets_Request;
use Google_Service_Sheets_ValueRange;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class GoogleSpreadsheetService {

    protected ?Google_Client $googleClient = null;
    protected ?Google_Service_Sheets $googleServiceSheets = null;

    protected ?string $spreadsheetId = null;

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

    /**
     * Получение данных из гугл таблицы
     * @return Collection
     */
    public function getData()
    {
        $resultData = collect();

        try {
            $range = 'A:F'; // Диапазон с полем "коммент"
            $response = $this->googleServiceSheets->spreadsheets_values->get($this->spreadsheetId, $range);
            $resultData = collect($response->getValues());
        } catch (Exception $e) {
            dd($e->getMessage());
        }

        return $resultData->map(function($item){
            return [
                "id" => $item[0],
                "name" => $item[1],
                "serial_passport" => $item[2],
                "number_passport" => $item[3],
                "status" => $item[4],
                "comment" => $item[5] ?? ''
            ];
        });
    }

    public function getDataArray()
    {
        return $this->getData()->toArray();
    }

    public function addRow(array $values): bool
    {
        // Колонки без "коммент"
        $range = 'A:E';

        //dd($values);
        $body = new Google_Service_Sheets_ValueRange([
            'values' => $values
        ]);

        $params = [
            'valueInputOption' => 'RAW' // 'RAW' — как есть, 'USER_ENTERED' — как при вводе
        ];

        $returnValue = false;

        try {
            $result = $this->googleServiceSheets->spreadsheets_values->append(
                $this->spreadsheetId,
                $range,
                $body,
                $params
            );

            $returnValue = true;

        } catch (Exception $e) {
            dd($e->getMessage());
        }

        return $returnValue;
    }

    /**
     * Обновить гугл строку
     * @param $rowIndex // отсчет с нуля
     * @param array $values
     * @return bool
     */
    public function updateRow($rowIndex, array $values): bool
    {
        // Колонки без "коммент"
        $range = 'A' . ($rowIndex + 1) . ':E' . ($rowIndex + 1); // Обновляем 3-ю строку, столбцы A, B, C

        $body = new Google_Service_Sheets_ValueRange([
            'values' => $values
        ]);

        $params = [
            'valueInputOption' => 'RAW' // 'RAW' — как есть, 'USER_ENTERED' — как при вводе
        ];

        $returnValue = false;

        try {
            $result = $this->googleServiceSheets->spreadsheets_values->update(
                $this->spreadsheetId,
                $range,
                $body,
                $params
            );

            $returnValue = true;

        } catch (Exception $e) {
            dd($e->getMessage());
        }

        return $returnValue;
    }

    /**
     * Удаление строки из гугл таблице
     * @param $rowIndex
     * @return bool
     */
    public function deleteRow($rowIndex): bool
    {
        $result = false;

        try {
            // Формируем запрос на удаление строки
            $requests = [
                new Google_Service_Sheets_Request([
                    'deleteDimension' => [
                        'range' => [
                            'sheetId' => 0, // ID листа (обычно 0 для первого листа)
                            'dimension' => 'ROWS',
                            'startIndex' => $rowIndex,
                            'endIndex' => $rowIndex + 1, // Удаляем 1 строку
                        ]
                    ]
                ])
            ];

            // Подготавливаем тело запроса
            $batchUpdateRequest = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
                'requests' => $requests
            ]);

            // Выполняем удаление
            $this->googleServiceSheets->spreadsheets->batchUpdate($this->spreadsheetId, $batchUpdateRequest);

            $result = true;

        } catch (Exception $e) {
            dd($e->getMessage());
        }

        return $result;
    }

    /**
     * Удаление строк из гугл таблице
     * @param $rowIndex
     * @return bool
     */
    public function deleteRows(array $rowsToDelete): bool
    {
        $result = false;

        try {

            // Формируем запрос на удаление строк

            $requests = [];

            foreach ($rowsToDelete as $row) {
                $requests[] = new Google_Service_Sheets_Request([
                    'deleteDimension' => [
                        'range' => [
                            'sheetId' => 0,
                            'dimension' => 'ROWS',
                            'startIndex' => $row,
                            'endIndex' => $row + 1
                        ]
                    ]
                ]);
            }

            // Подготавливаем тело запроса
            $batchUpdateRequest = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
                'requests' => $requests
            ]);

            // Выполняем удаление
            $this->googleServiceSheets->spreadsheets->batchUpdate($this->spreadsheetId, $batchUpdateRequest);

            $result = true;

        } catch (Exception $e) {
            dd($e->getMessage());
        }

        return $result;
    }


}
