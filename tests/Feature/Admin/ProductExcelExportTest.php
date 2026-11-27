<?php

namespace Tests\Feature\Admin;

use App\Enums\ProductStatus;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\Products\ProductExcelExport;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ProductExcelExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_can_select_one_or_many_columns_and_includes_all_pages(): void
    {
        $admin = User::factory()->admin()->create();
        $brand = Brand::factory()->create(['name' => 'Test brand']);
        $category = Category::factory()->create(['name' => 'Test category']);
        Product::factory()->count(100)->create([
            'brand_id' => $brand->id, 'category_id' => $category->id,
            'unit_price' => '12.50',
        ]);
        Product::factory()->create([
            'name' => '=SUM(1+1)', 'sku' => '001234', 'unit_price' => null,
            'brand_id' => $brand->id, 'category_id' => $category->id,
        ]);
        Product::factory()->create([
            'name' => 'Archived product', 'status' => ProductStatus::Archived,
            'unit_price' => '3.25', 'brand_id' => $brand->id, 'category_id' => $category->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.products.export', [
            'scope' => 'all', 'columns' => ['name', 'sku', 'unit_price'], 'page' => 2,
        ]));
        $response->assertOk()->assertDownload();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $sheet = $this->worksheet($response);
        $this->assertSame(103, $sheet->query('//s:sheetData/s:row')->length);
        $this->assertSame(3, $sheet->query('//s:row[@r="1"]/s:c')->length);
        $this->assertSame('Product name', $sheet->evaluate('string(//s:c[@r="A1"]/s:is/s:t)'));
        $this->assertSame('SKU / Item code', $sheet->evaluate('string(//s:c[@r="B1"]/s:is/s:t)'));
        $this->assertSame('Price (USD)', $sheet->evaluate('string(//s:c[@r="C1"]/s:is/s:t)'));
        $this->assertSame('A1:C103', $sheet->evaluate('string(//s:autoFilter/@ref)'));
        $this->assertSame('frozen', $sheet->evaluate('string(//s:pane/@state)'));
        $this->assertSame(0, $sheet->query('//s:f')->length);
        $row = '//s:row[s:c/s:is/s:t="=SUM(1+1)"]';
        $this->assertSame('001234', $sheet->evaluate('string('.$row.'/s:c[2]/s:is/s:t)'));
        $this->assertSame(0, $sheet->query($row.'/s:c[3]/s:v')->length);
        $this->assertSame(100, $sheet->query('//s:c[@t="n"]/s:v[.="12.50"]')->length);

        $single = $this->worksheet($this->actingAs($admin)->get(route('admin.products.export', [
            'scope' => 'all', 'columns' => ['name'],
        ]))->assertOk()->assertDownload());
        $this->assertSame(1, $single->query('//s:row[@r="1"]/s:c')->length);
        $this->assertSame(103, $single->query('//s:sheetData/s:row')->length);

        $all = $this->worksheet($this->actingAs($admin)->get(route('admin.products.export', [
            'scope' => 'all', 'columns' => array_keys(ProductExcelExport::columns()),
        ]))->assertOk()->assertDownload());
        $this->assertSame(count(ProductExcelExport::columns()), $all->query('//s:row[@r="1"]/s:c')->length);
        $this->assertSame('Test category', $all->evaluate('string(//s:c[@r="C2"]/s:is/s:t)'));
        $this->assertSame('Test brand', $all->evaluate('string(//s:c[@r="D2"]/s:is/s:t)'));
    }

    public function test_filtered_export_uses_applied_filters_and_empty_results_keep_headers(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        Product::factory()->count(18)->create([
            'category_id' => $category->id, 'brand_id' => $brand->id, 'status' => ProductStatus::Published,
        ]);
        Product::factory()->create([
            'category_id' => $category->id, 'brand_id' => $brand->id, 'status' => ProductStatus::Archived,
        ]);
        Product::factory()->create();

        $sheet = $this->worksheet($this->actingAs($admin)->get(route('admin.products.export', [
            'scope' => 'filtered', 'columns' => ['name'], 'category' => $category->id,
            'brand' => $brand->id, 'status' => 'published',
        ]))->assertOk()->assertDownload());
        $this->assertSame(19, $sheet->query('//s:sheetData/s:row')->length);

        $empty = $this->worksheet($this->actingAs($admin)->get(route('admin.products.export', [
            'scope' => 'filtered', 'columns' => ['name'], 'search' => 'no-matching-product-xyz',
        ]))->assertOk()->assertDownload());
        $this->assertSame(1, $empty->query('//s:sheetData/s:row')->length);
    }

    public function test_export_requires_admin_and_valid_columns(): void
    {
        $url = route('admin.products.export', ['scope' => 'all', 'columns' => ['name']]);
        $this->getJson($url)->assertUnauthorized();
        // Admin middleware intentionally hides protected routes from non-admins.
        $this->actingAs(User::factory()->create())->getJson($url)->assertNotFound();
        $this->actingAs(User::factory()->admin()->create())->getJson(route('admin.products.export', [
            'scope' => 'all', 'columns' => ['password'],
        ]))->assertUnprocessable()->assertJsonValidationErrors('columns.0');
        $this->getJson(route('admin.products.export', [
            'scope' => 'all',
        ]))->assertUnprocessable()->assertJsonValidationErrors('columns');
    }

    private function worksheet(TestResponse $response): DOMXPath
    {
        $path = $response->baseResponse->getFile()->getPathname();
        try {
            if (class_exists(\ZipArchive::class)) {
                $zip = new \ZipArchive;
                $this->assertTrue($zip->open($path) === true);
                try {
                    $xml = $zip->getFromName('xl/worksheets/sheet1.xml');
                    $this->assertNotFalse($zip->getFromName('[Content_Types].xml'));
                    $this->assertNotFalse($zip->getFromName('xl/styles.xml'));
                } finally {
                    $zip->close();
                }
            } else {
                $zip = new \PharData($path);
                $xml = $zip['xl/worksheets/sheet1.xml']->getContent();
                $this->assertTrue(isset($zip['[Content_Types].xml'], $zip['xl/styles.xml']));
                unset($zip);
            }
            $this->assertIsString($xml);
            $doc = new DOMDocument;
            $this->assertTrue($doc->loadXML($xml, LIBXML_NONET));
            $xpath = new DOMXPath($doc);
            $xpath->registerNamespace('s', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

            return $xpath;
        } finally {
            @unlink($path);
        }
    }
}
