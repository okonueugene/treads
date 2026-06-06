<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class ProductCsvImporter
{
    public function __construct(
        private readonly User $vendor,
    ) {}

    /**
     * @return array{imported: int, updated: int, skipped: int, errors: list<string>}
     */
    public function import(string $path): array
    {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new RuntimeException('Unable to open CSV file.');
        }

        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);
            throw new RuntimeException('CSV file is empty.');
        }

        $header = array_map(fn ($col) => strtolower(trim($col)), $header);
        $stats = ['imported' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            $data = array_combine($header, $row);

            if ($data === false) {
                $stats['errors'][] = "Row {$rowNumber}: column count mismatch.";
                $stats['skipped']++;

                continue;
            }

            $validator = Validator::make($data, [
                'sku' => ['required', 'string', 'max:100'],
                'title' => ['required', 'string', 'max:255'],
                'price' => ['required', 'numeric', 'min:0'],
                'stock' => ['required', 'integer', 'min:0'],
                'width' => ['required', 'integer', 'min:100', 'max:400'],
                'aspect_ratio' => ['required', 'integer', 'min:25', 'max:90'],
                'rim_diameter' => ['required', 'integer', 'min:10', 'max:30'],
            ]);

            if ($validator->fails()) {
                $stats['errors'][] = "Row {$rowNumber}: ".$validator->errors()->first();
                $stats['skipped']++;

                continue;
            }

            $payload = [
                'brand_id' => $data['brand_id'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'price' => $data['price'],
                'compare_price' => $data['compare_price'] ?? null,
                'stock' => (int) $data['stock'],
                'condition' => in_array($data['condition'] ?? 'new', ['new', 'used'], true) ? $data['condition'] : 'new',
                'condition_grade' => $data['condition_grade'] ?? null,
                'tread_depth_mm' => $data['tread_depth_mm'] ?? null,
                'dot_week' => isset($data['dot_week']) ? (int) $data['dot_week'] : null,
                'dot_year' => isset($data['dot_year']) ? (int) $data['dot_year'] : null,
                'remaining_mileage_km' => isset($data['remaining_mileage_km']) ? (int) $data['remaining_mileage_km'] : null,
                'defects' => $data['defects'] ?? null,
                'width' => (int) $data['width'],
                'aspect_ratio' => (int) $data['aspect_ratio'],
                'rim_diameter' => (int) $data['rim_diameter'],
                'load_index' => $data['load_index'] ?? null,
                'speed_rating' => $data['speed_rating'] ?? null,
                'season' => $data['season'] ?? 'all-season',
                'image' => $data['image'] ?? null,
                'is_active' => filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOL),
            ];

            $existing = Product::query()
                ->where('vendor_id', $this->vendor->id)
                ->where('sku', $data['sku'])
                ->exists();

            Product::query()->updateOrCreate(
                [
                    'vendor_id' => $this->vendor->id,
                    'sku' => $data['sku'],
                ],
                $payload,
            );

            if ($existing) {
                $stats['updated']++;
            } else {
                $stats['imported']++;
            }
        }

        fclose($handle);

        return $stats;
    }
}
