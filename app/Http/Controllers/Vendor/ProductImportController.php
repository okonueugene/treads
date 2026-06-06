<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Services\ProductCsvImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductImportController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $request->validate([
            'csv' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        $vendor = $request->user();

        abort_unless($vendor->is_vendor, 403);

        $path = $request->file('csv')->getRealPath();
        $stats = (new ProductCsvImporter($vendor))->import($path);

        return back()->with('import_stats', $stats);
    }
}
