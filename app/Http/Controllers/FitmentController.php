<?php

namespace App\Http\Controllers;

use App\Models\FitmentData;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FitmentController extends Controller
{
    public function lookup(Request $request): View
    {
        $validated = $request->validate([
            'make' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'year' => ['required', 'integer', 'min:1980', 'max:2030'],
        ]);

        $make = $validated['make'];
        $model = $validated['model'];
        $year = (int) $validated['year'];

        $fitments = FitmentData::query()
            ->forVehicle($make, $model, $year)
            ->get(['width', 'aspect_ratio', 'rim_diameter']);

        $products = collect();

        if ($fitments->isNotEmpty()) {
            $sizes = $fitments->map(fn ($f) => [
                'width' => $f->width,
                'aspect_ratio' => $f->aspect_ratio,
                'rim_diameter' => $f->rim_diameter,
            ])->unique()->values()->all();

            $products = Product::query()
                ->active()
                ->inStock()
                ->where(function ($query) use ($sizes) {
                    foreach ($sizes as $size) {
                        $query->orWhere(function ($q) use ($size) {
                            $q->where('width', $size['width'])
                                ->where('aspect_ratio', $size['aspect_ratio'])
                                ->where('rim_diameter', $size['rim_diameter']);
                        });
                    }
                })
                ->with('brand')
                ->limit(50)
                ->get()
                ->map(fn (Product $p) => [
                    'title' => $p->title,
                    'size' => $p->formattedSize(),
                    'price' => '$'.number_format($p->price, 2),
                    'img' => product_image_url($p->image),
                    'id' => $p->id,
                ]);
        }

        return view('partials.fitment_results', [
            'products' => $products->all(),
            'make' => $make,
            'model' => $model,
            'year' => $year,
        ]);
    }
}
