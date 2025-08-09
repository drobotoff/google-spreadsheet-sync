<?php

namespace App\Console\Commands;

use App\Enums\StatusEnum;
use App\Models\Person;
use App\Service\GoogleSpreadsheetService;
use Illuminate\Console\Command;

class SyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync {count?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Синхронизация с гугл таблицей';

    /**
     * Удаляет N строк выше (для перерисовки логов)
     */
    private function clearLines(int $lines): void
    {
        for ($i = 0; $i < $lines; $i++) {
            $this->output->write("\x0D\x1B[2K\x1B[1A"); // Возврат каретки + очистка строки + вверх
        }
    }

    /**
     * Execute the console command.
     */
    public function handle(GoogleSpreadsheetService $googleSpreadsheetService)
    {
        $startTime = microtime(true);

        // Получаем данные для выгрузки
        $count = (int) $this->argument('count');

        if ($count == 0) {
            $dataOfDB = Person::allowed()->get();
        } else {
            $dataOfDB = Person::allowed()->limit($count)->get();
        }

        // Получаем данные из гугл таблицы
        $sheetDataArray = $googleSpreadsheetService->getDataArray();

        // Создаем прогресс-бар
        $progressBar = $this->output->createProgressBar($dataOfDB->count());

        // Устанавливаем формат отображения прогресс-бара
        $progressBar->setFormat(
            '%current%/%max% %bar% %percent:3s%%'
        );

        // Запускаем прогресс-бар
        $progressBar->start();

        $logBuffer = [];
        $maxLines = 10;

        // Массив для удаления строк
        $rowsToDelete = [];

        // Выгрузка
        foreach ($dataOfDB as $person) {

            $foundRowInSheet = false;

            foreach ($sheetDataArray as $sheetRowIndex => $sheetRowItem) {

                // Обновляем записи в гугл таблице, если записи существует

                if ($sheetRowItem['id'] == $person['id']) // Предполагаю что ИД в БД и ИД в гугл таблице должны совпадать
                {
                    $foundRowInSheet = true;

                    if ($sheetRowItem['status'] == StatusEnum::ALLOWED->value) {
                        if ( $sheetRowItem['name'] != $person->name
                            || $sheetRowItem['serial_passport'] != $person->serial_passport
                            || $sheetRowItem['number_passport'] != $person->number_passport
                        ) {
                            // Обновляем запись в гугл таблице
                            $arrayToUpdate = array_values($person->toArray());
                            $arrayToUpdate[0] = (string) $arrayToUpdate[0]; // без этой строки тоже будет работать, просто переводит id в строку
                            $googleSpreadsheetService->updateRow($sheetRowIndex, [$arrayToUpdate]);
                        }
                    }

                    if ($sheetRowItem['status'] == StatusEnum::PROHIBITED->value) {
                        $personId = $person['id'];
                        $rowsToDelete[$personId] = $sheetRowIndex;
                    }

                    $logBuffer[] = "ID: " . implode(" | ", $person->toArray()) . ' | ' . $sheetRowItem['comment'];
                }
            }

            // Если запись в гугл таблице не найдено, добавляем в конец таблицы
            if (!$foundRowInSheet) {
                $arrayToAppend = array_values($person->toArray());
                $arrayToAppend[0] = (string) $arrayToAppend[0]; // без этой строки тоже будет работать, просто переводит id в строку
                $googleSpreadsheetService->addRow([$arrayToAppend]);
                $logBuffer[] = "ID: " . implode(" | ", $person->toArray());
            }

            $recent = array_slice($logBuffer, -$maxLines);

            // Очищаем последние N строк
            $this->clearLines(count($recent));

            // Выводим последние логи
            foreach ($recent as $log) {
                $this->line($log);
            }

            // Обновляем прогресс-бар
            $progressBar->advance();
        }

        // Завершаем прогресс-бар
        $progressBar->finish();

        $this->newLine();

        // Удаляем отмеченые строки
        if (count($rowsToDelete) > 0) {
            $this->info('Удаление строк из таблицы');
            // Удаление строк из таблицы
            $rowsToDeleteIds = array_keys($rowsToDelete);
            foreach ($rowsToDeleteIds as $deleteId) {
                $sheetDataArrayUpdated = $googleSpreadsheetService->getDataArray();
                foreach ($sheetDataArrayUpdated as $index => $value) {
                    if ($deleteId == $value['id']) {
                        $resultDelete = $googleSpreadsheetService->deleteRow($index);
                        if ($resultDelete) {
                            $this->error('ID: ' . implode(' | ', array_values($value)));
                            Person::where('id', $value['id'])
                                ->update(['status' => StatusEnum::PROHIBITED->value]);
                        }
                    }
                }
            }
        }


        // Выводим финальное сообщение
        $time = microtime(true) - $startTime;
        $this->info("Время выполнения:" . number_format($time, 4) . " сек.");
        $this->info('Синхронизация завершена!');
    }
}
