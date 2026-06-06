<?php

namespace Database\Seeders;

use App\Models\FitmentData;
use Illuminate\Database\Seeder;

class FitmentDataSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/fitment_sample.csv');
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return;
        }

        $header = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);

            FitmentData::query()->updateOrCreate(
                [
                    'make' => $data['make'],
                    'model' => $data['model'],
                    'year_from' => (int) $data['year_from'],
                    'year_to' => (int) $data['year_to'],
                    'width' => (int) $data['width'],
                    'aspect_ratio' => (int) $data['aspect_ratio'],
                    'rim_diameter' => (int) $data['rim_diameter'],
                    'position' => $data['position'] ?? 'all',
                ],
                [
                    'trim' => $data['trim'] ?? null,
                ],
            );
        }

        fclose($handle);
    }
}
