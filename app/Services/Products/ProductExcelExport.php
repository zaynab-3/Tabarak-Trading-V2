<?php

namespace App\Services\Products;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use RuntimeException;
use Throwable;

class ProductExcelExport
{
    private const NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    /** @return array<string, array{label: string, width: int, type: string}> */
    public static function columns(): array
    {
        return [
            'name' => ['label' => 'Product name', 'width' => 42, 'type' => 'text'],
            'sku' => ['label' => 'SKU / Item code', 'width' => 22, 'type' => 'text'],
            'category' => ['label' => 'Category', 'width' => 25, 'type' => 'text'],
            'brand' => ['label' => 'Brand', 'width' => 25, 'type' => 'text'],
            'unit_price' => ['label' => 'Price (USD)', 'width' => 18, 'type' => 'money'],
            'weight' => ['label' => 'Net weight', 'width' => 16, 'type' => 'text'],
            'pack_quantity' => ['label' => 'Pack quantity', 'width' => 18, 'type' => 'number'],
            'unit_label' => ['label' => 'Unit label', 'width' => 18, 'type' => 'text'],
            'open_quantity' => ['label' => 'Open quantity', 'width' => 18, 'type' => 'text'],
            'stock_quantity' => ['label' => 'Stock quantity', 'width' => 18, 'type' => 'number'],
            'status' => ['label' => 'Status', 'width' => 17, 'type' => 'text'],
            'featured' => ['label' => 'Featured', 'width' => 14, 'type' => 'text'],
            'short_description' => ['label' => 'Short description', 'width' => 48, 'type' => 'text'],
            'description' => ['label' => 'Description', 'width' => 60, 'type' => 'text'],
            'id' => ['label' => 'Product ID', 'width' => 15, 'type' => 'number'],
            'updated_at' => ['label' => 'Updated (US Eastern)', 'width' => 26, 'type' => 'text'],
        ];
    }

    /** @param list<string> $selected */
    public function create(Builder $query, array $selected): string
    {
        $columns = array_intersect_key(self::columns(), array_fill_keys($selected, true));
        if ($columns === []) {
            throw new RuntimeException('Select at least one export column.');
        }

        $sheet = tempnam(sys_get_temp_dir(), 'tabarak-xlsx-');
        if ($sheet === false) {
            throw new RuntimeException('Cannot create the Excel export file.');
        }
        $path = $sheet.'.zip';
        $stream = null;

        try {
            $stream = fopen($sheet, 'wb');
            if ($stream === false) {
                throw new RuntimeException('Cannot open the Excel worksheet.');
            }
            $write = static function (string $xml) use ($stream): void {
                if (fwrite($stream, $xml) !== strlen($xml)) {
                    throw new RuntimeException('Cannot write the Excel worksheet.');
                }
            };
            $last = $this->letter(count($columns));
            $write('<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                .'<worksheet xmlns="'.self::NS.'">'
                .'<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/><selection pane="bottomLeft" activeCell="A2" sqref="A2"/></sheetView></sheetViews>'
                .'<sheetFormatPr defaultRowHeight="20"/><cols>');
            $index = 0;
            foreach ($columns as $column) {
                $index++;
                $write('<col min="'.$index.'" max="'.$index.'" width="'.$column['width'].'" customWidth="1"/>');
            }
            $write('</cols><sheetData><row r="1" ht="28" customHeight="1">');
            $index = 0;
            foreach ($columns as $column) {
                $write($this->cell($this->letter(++$index).'1', $column['label'], 'text', 1));
            }
            $write('</row>');

            $row = 1;
            foreach ($query->reorder('name')->orderBy('id')->lazy(500) as $product) {
                if (++$row > 1048576) {
                    throw new RuntimeException('The catalogue exceeds Excel row limits. Export a filtered list.');
                }
                $values = [];
                $lines = 1;
                foreach ($columns as $key => $column) {
                    $value = $values[$key] = $this->value($product, $key);
                    if ($column['type'] === 'text' && $value !== null) {
                        $wrapped = 0;
                        foreach (preg_split('/\R/u', (string) $value) ?: [''] as $line) {
                            $wrapped += max(1, (int) ceil(mb_strwidth($line) / max(1, $column['width'] - 3)));
                        }
                        $lines = max($lines, $wrapped);
                    }
                }
                $height = min(120, max(20, $lines * 14 + 6));
                $write('<row r="'.$row.'" ht="'.$height.'" customHeight="1">');
                $index = 0;
                foreach ($columns as $key => $column) {
                    $style = $column['type'] === 'money' ? ($row % 2 === 0 ? 4 : 5) : ($row % 2 === 0 ? 2 : 3);
                    $write($this->cell($this->letter(++$index).$row, $values[$key], $column['type'], $style));
                }
                $write('</row>');
            }
            $write('</sheetData><autoFilter ref="A1:'.$last.$row.'"/></worksheet>');
            fclose($stream);
            $stream = null;

            $entries = $this->packageEntries();
            // ZIP is preferred; PharData supports ZIP on installations without ext-zip.
            if (class_exists(\ZipArchive::class)) {
                $zip = new \ZipArchive;
                if ($zip->open($path, \ZipArchive::CREATE | \ZipArchive::EXCL) !== true) {
                    throw new RuntimeException('Cannot create the Excel archive.');
                }
                try {
                    foreach ($entries as $name => $xml) {
                        if (! $zip->addFromString($name, $xml)) {
                            throw new RuntimeException('Cannot add Excel metadata.');
                        }
                    }
                    if (! $zip->addFile($sheet, 'xl/worksheets/sheet1.xml')) {
                        throw new RuntimeException('Cannot add the Excel worksheet.');
                    }
                } finally {
                    if (! $zip->close()) {
                        throw new RuntimeException('Cannot finalize the Excel archive.');
                    }
                }
            } elseif (class_exists(\PharData::class)) {
                $zip = new \PharData($path, 0, null, \Phar::ZIP);
                foreach ($entries as $name => $xml) {
                    $zip->addFromString($name, $xml);
                }
                $zip->addFile($sheet, 'xl/worksheets/sheet1.xml');
                unset($zip);
            } else {
                throw new RuntimeException('Excel export requires PHP ZipArchive or PharData.');
            }

            clearstatcache(true, $path);

            return $path;
        } catch (Throwable $exception) {
            if (is_file($path)) {
                @unlink($path);
            }
            throw $exception;
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
            @unlink($sheet);
        }
    }

    private function value(Product $product, string $key): mixed
    {
        return match ($key) {
            'category' => $product->category?->name,
            'brand' => $product->brand?->name,
            'weight' => $product->weight_value === null ? null : trim(rtrim(rtrim((string) $product->weight_value, '0'), '.').' '.$product->weight_unit),
            'open_quantity' => $product->allows_open_quantity ? 'Yes' : 'No',
            'stock_quantity' => $product->allows_open_quantity ? null : $product->stock_quantity,
            'featured' => $product->is_featured ? 'Yes' : 'No',
            'status' => ucfirst($product->status->value),
            'updated_at' => $product->updated_at?->copy()->setTimezone('America/Detroit')->format('Y-m-d H:i'),
            default => $product->getAttribute($key),
        };
    }

    private function letter(int $index): string
    {
        $letter = '';
        while ($index > 0) {
            $index--;
            $letter = chr(65 + $index % 26).$letter;
            $index = intdiv($index, 26);
        }
        return $letter;
    }

    private function cell(string $ref, mixed $value, string $type, int $style): string
    {
        $start = '<c r="'.$ref.'" s="'.$style.'"';
        if ($value === null || $value === '') {
            return $start.'/>';
        }
        if (($type === 'number' || $type === 'money') && is_numeric($value)) {
            return $start.' t="n"><v>'.$this->escape((string) $value).'</v></c>';
        }
        // Explicit inline strings keep SKUs intact and prevent formula injection.
        return $start.' t="inlineStr"><is><t xml:space="preserve">'.$this->escape(mb_substr((string) $value, 0, 32767)).'</t></is></c>';
    }

    private function escape(string $value): string
    {
        $value = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $value) ?? '';
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /** @return array<string, string> */
    private function packageEntries(): array
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $relationships = 'http://schemas.openxmlformats.org/package/2006/relationships';
        $office = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
        $content = 'application/vnd.openxmlformats-officedocument.spreadsheetml.';
        return [
            '[Content_Types].xml' => $xml.'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="'.$content.'sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="'.$content.'worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="'.$content.'styles+xml"/></Types>',
            '_rels/.rels' => $xml.'<Relationships xmlns="'.$relationships.'"><Relationship Id="rId1" Type="'.$office.'/officeDocument" Target="xl/workbook.xml"/></Relationships>',
            'xl/workbook.xml' => $xml.'<workbook xmlns="'.self::NS.'" xmlns:r="'.$office.'"><bookViews><workbookView/></bookViews><sheets><sheet name="Products" sheetId="1" r:id="rId1"/></sheets></workbook>',
            'xl/_rels/workbook.xml.rels' => $xml.'<Relationships xmlns="'.$relationships.'"><Relationship Id="rId1" Type="'.$office.'/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="'.$office.'/styles" Target="styles.xml"/></Relationships>',
            'xl/styles.xml' => $xml.$this->styles(),
        ];
    }

    private function styles(): string
    {
        return '<styleSheet xmlns="'.self::NS.'">'
            .'<numFmts count="1"><numFmt numFmtId="164" formatCode="&quot;$&quot;#,##0.00"/></numFmts>'
            .'<fonts count="2"><font><sz val="11"/><color rgb="FF19202C"/><name val="Calibri"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font></fonts>'
            .'<fills count="5"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF192B48"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFFFFFFF"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFF1F4F8"/><bgColor indexed="64"/></patternFill></fill></fills>'
            .'<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left/><right/><top/><bottom style="thin"><color rgb="FFE2E8F0"/></bottom><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="6"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="4" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>'
            .'<xf numFmtId="164" fontId="0" fillId="3" borderId="1" xfId="0" applyNumberFormat="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" horizontal="right"/></xf>'
            .'<xf numFmtId="164" fontId="0" fillId="4" borderId="1" xfId="0" applyNumberFormat="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" horizontal="right"/></xf></cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
    }
}
