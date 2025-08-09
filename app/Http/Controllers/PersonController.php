<?php

namespace App\Http\Controllers;

use App\Enums\StatusEnum;
use App\Models\Person;
use App\Service\GoogleSpreadsheetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class PersonController extends Controller
{
    /**
     * Создает запись
     */
    public function store(Request $request): JsonResponse
    {
        $person = new Person($request->all());
        $createStatus = $person->save();
        return response()->json(['message' => 'Create is successfully', 'status' => $createStatus]);
    }

    /**
     * Выбирает одну запись для просмотра
     * @param Person $person
     * @return JsonResponse
     */
    public function show(Person $person): JsonResponse
    {
        return response()->json($person->toArray());
    }

    /**
     * Обновить запись
     * @param Request $request
     * @param Person $person
     * @return JsonResponse
     */
    public function update(Request $request, Person $person): JsonResponse
    {
        $updatedStatus = $person->update($request->toArray());
        return response()->json(['message' => 'Update is successfully', 'status' => $updatedStatus]);
    }

    /**
     * Удаляет запись
     * @param Person $person
     * @return JsonResponse
     */
    public function destroy(Person $person, GoogleSpreadsheetService $googleSpreadsheetService): JsonResponse
    {
        $deleteStatus = false;
        $deleteId = $person->id;
        $sheetDataArrayUpdated = $googleSpreadsheetService->getDataArray();
        foreach ($sheetDataArrayUpdated as $index => $value) {
            if ($deleteId == $value['id']) {
                $resultDelete = $googleSpreadsheetService->deleteRow($index);
                if ($resultDelete) {
                    $deleteStatus = $person->delete();
                }
            }
        }
        return response()->json(['message' => 'Delete is successfully', 'status' => $deleteStatus]);
    }

    public function getData(GoogleSpreadsheetService $googleSpreadsheetService): JsonResponse
    {
        $dataOfDB = collect(Person::all()->toArray());

        $personsOfDB = $dataOfDB->map(function($item) {
            $item['comment'] = "";
            return $item;
        });

        $dataOfSpreadSheet = collect($googleSpreadsheetService->getData());

        $result = [];
        foreach($personsOfDB as $key => $person) {
            $found = $dataOfSpreadSheet->where(function($item) use ($person){
                return ($item['id'] == $person['id']) &&
                    (trim($item['name']) === trim($person['name'])) &&
                    ($item['serial_passport'] === $person['serial_passport']) &&
                    ($item['number_passport'] === $person['number_passport']) &&
                    ($item['status'] === $person['status']);
            });
            if ($found->count() == 1) {
                $result[] = $found->first();
            } else {
                $result[] = $person;
            }
        }

        return response()->json($result);
    }

    /**
     * Очистить базу данных
     * @return JsonResponse
     */
    public function clearDatabase(): JsonResponse
    {
        Person::whereNotNull('id')->delete();
        return response()->json(['message' => 'Database is cleared']);
    }

    /**
     * Генерировать новые запись в базе данных, предыдущие не удаляются
     * @return JsonResponse
     */
    public function generateDataInDatabase(): JsonResponse
    {
        // Запускаем seeder
        Artisan::call('db:seed', [
            '--class' => 'PersonSeeder',  // Указать конкретный seeder
            '--force' => true,          // Обязательно в production-like окружениях
        ]);

        // Получаем вывод
        $output = Artisan::output();

        return response()->json([
            'message' => 'Data is generated',
            'output' => $output
        ]);
    }
}
