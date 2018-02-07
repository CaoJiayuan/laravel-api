<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/2/7
 * Time: 下午5:09
 */

namespace CaoJiayuan\LaravelApi\Database\Eloquent\Helpers;

use CaoJiayuan\LaravelApi\Database\Eloquent\ExcelFormat;
use CaoJiayuan\LaravelApi\Database\Eloquent\Exceptions\InvalidImportFormatException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Readers\LaravelExcelReader;
use Maatwebsite\Excel\Writers\LaravelExcelWriter;
use PHPExcel_Cell_DataValidation;

trait ExcelEntity
{


    protected $excelFormatMap = [
        'string' => ExcelFormat::FORMAT_TEXT,
        'date'   => ExcelFormat::FORMAT_DATE_YYYYMMDD2,
        'int'    => ExcelFormat::FORMAT_NUMBER,
        'float'  => ExcelFormat::FORMAT_NUMBER_00,
    ];

    public function getEnumValues($key)
    {
        $t = ucfirst(camel_case($key));
        $method = "get{$t}Enums";

        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return [];
    }


    public function getExcelCastFormat($cast)
    {
        return array_get($this->getExcelFormatMap(), $cast, ExcelFormat::FORMAT_TEXT);
    }


    /**
     * @return array
     */
    public function getImportTemplateRow()
    {
        return [

        ];
    }


    public function getImportTemplateName()
    {
        return md5(get_class($this));
    }


    public function getImportTemplate()
    {
        $excelType = $this->getExcelType();
        $dir = storage_path('app' . DIRECTORY_SEPARATOR . $excelType . DIRECTORY_SEPARATOR . 'template');
        $cacheName = $this->getImportTemplateName();
        $templatePath = $dir . DIRECTORY_SEPARATOR . $cacheName . '.' . $excelType;

        $cache = $this->shouldCacheTemplate();
        if (!file_exists($templatePath) || !$cache) {
            Excel::create($cacheName, function ($excel) {
                /** @var LaravelExcelWriter $excel */
                $excel->sheet('sheet1', function ($sheet) {
                    /** @var LaravelExcelWorksheet $sheet */
                    $headers = $this->getExcelHeaders();
                    $array = [array_values($headers)];
                    $columns = range('A', 'Z');
                    $c = 0;
                    foreach ($headers as $key => $name) {
                        $column = $columns[$c];
                        $this->setExcelColumnCast($sheet, $key, $column);
                        $c++;
                    }
                    $template = $this->getImportTemplateRow();
                    if ($template instanceof Arrayable) {
                        $template = $template->toArray();
                    }

                    $template && array_push($array, $template);
                    $sheet->fromArray($array, null, 'A1', false, false)->freezeFirstRow();
                });
            })->store($excelType, $dir);
        }
        $storageDir = $excelType . DIRECTORY_SEPARATOR . 'template';
        $path = $storageDir . DIRECTORY_SEPARATOR . $cacheName . '.' . $excelType;
        $filename = $cacheName . '.' . $excelType;
        if (Storage::exists($path) && $cache) {
            return Storage::url($path);
        }

        $file = new UploadedFile($templatePath, $filename);

        $p = $file->storePubliclyAs($storageDir, $filename);

        return Storage::url($p);
    }

    public function importExcel($file)
    {
        Excel::load($file, function ($reader){
            /** @var LaravelExcelReader $reader */
            foreach ($reader as $sheet) {
                $rows = $this->getImportRows($sheet);
                $this->validateImport($rows);

                try {
                    $this->insert($rows);
                } catch (\Exception $exception) {
                    $this->handleImportException($exception);
                }
                break;
            }
        });
    }

    public function exportExcel(Collection $models, $name)
    {
        $headers = $this->getExcelHeaders();
        $excelType = $this->getExcelType();
        $dir = storage_path('app/' . $excelType);
        $cacheName = str_random(32);
        Excel::create($cacheName, function ($excel) use ($models, $headers) {
            /** @var \Maatwebsite\Excel\Writers\LaravelExcelWriter $excel */
            $excel->sheet('表格1', function($sheet) use ($models, $headers) {
                /** @var \Maatwebsite\Excel\Classes\LaravelExcelWorksheet $sheet */
                $array = $models->map(function ($model) use ($models, $headers) {
                    /** @var Model $model */
                    $result = [];
                    foreach ($headers as $key => $v) {
                        $format = ucfirst(camel_case($key));
                        $changer = "get{$format}ExportValue";
                        if (method_exists($this, $changer)) {
                            $result[$key] = $this->$changer($model->$key, $model);
                        } else {
                            $result[$key] = $model->$key;
                        }
                    }

                    return $result;
                })->toArray();

                array_unshift($array, $headers);

                $sheet->fromArray($array, '', 'A1', false, false);
            });
        })->store($excelType, $dir);
        $path = $dir . DIRECTORY_SEPARATOR . $cacheName. '.' . $excelType;
        $filename = $name . '.' . $excelType;
        $file = new UploadedFile($path, $filename);

        $path = $file->storePubliclyAs($excelType, $filename);

        return Storage::url($path);
    }

    /**
     * @param \PHPExcel $sheet
     * @param bool $withOutFirstRow
     * @return array
     */
    public function getImportRows($sheet, $withOutFirstRow = true)
    {
        $headers = $this->getExcelHeaders();

        $active = $sheet->getActiveSheet();
        $interator = ($active->getRowIterator());
        $rows = [];
        $ke = 0;
        if ($withOutFirstRow) {
            $head = $this->getExcelFirstRow($interator);
            $interator->resetStart(2);
        } else {
            $head = array_values($headers);
        }

        $keys = array_flip($headers);
        $countColumn = count($head);
        foreach ($interator as $inter) {
            $i = 0;
            $cells = [];
            $countNull = 0;
            foreach ($inter->getCellIterator() as $item) {
                /** @var \PHPExcel_Cell $item */
                $name = array_get($head, $i);
                if ($name && $key = array_get($keys, $name)) {
                    $value = $item->getValue();
                    if ($value == null){
                        $countNull ++;
                    }
                    $cell = $this->castImportValue($key, $value);

                    $cells[$key] = $cell;
                }
                $i++;
            }
            if ($countNull >= $countColumn) {
                break;
            }

            $rows[$ke] = array_merge($this->getDefaultImportValues(), $cells);

            $ke++;
        }

        return $rows;
    }


    public function castImportValue($key, $value)
    {
        $t = ucfirst(camel_case($key));
        $method = "getImport{$t}Value";

        if (method_exists($this, $method)) {
            return $this->$method($value);
        }

        return $value;
    }

    protected function validateImport($list)
    {

    }

    /**
     * @param \Exception $e
     * @throws \Exception
     */
    protected function handleImportException($e)
    {
        throw $e;
    }


    /**
     * @return array
     */
    public function getDefaultImportValues()
    {
        return [];
    }

    /**
     * @param \PHPExcel_Worksheet_RowIterator $interator
     * @param bool $validate
     * @return array
     */
    protected function getExcelFirstRow($interator, $validate = true)
    {
        $row = [];
        foreach ($interator as $inter) {
            foreach ($inter->getCellIterator() as $item) {
                /** @var \PHPExcel_Cell $item */
                $value = $item->getValue();
                $value && $row[] = $value;
            }
            break;
        }
        if ($validate) {
            $valid = true;
            $excelHeaders = $this->getExcelHeaders();
            if (count($row) != count($excelHeaders)) {
                $valid = false;
            } else {
                $has = 0;
                $r = array_unique($row);
                $headers = array_values($excelHeaders);
                foreach ($r as $item) {
                    if (in_array($item, $headers)) {
                        $has++;
                    }
                }
                if ($has != count($headers)) {
                    $valid = false;
                }
            }

            if (!$valid) {
                $this->handleInvalidImportFormatException();
            }
        }

        return $row;
    }

    protected function handleInvalidImportFormatException()
    {
        throw new InvalidImportFormatException('导入数据格式不正确，请检查一下导入的数据格式');
    }

    /**
     * @return array
     */
    public function getExcelFormatMap()
    {
        return $this->excelFormatMap;
    }

    /**
     * @param LaravelExcelWorksheet $sheet
     * @param $key
     * @param $column
     * @param int $firstRow
     * @param int $numOfRow
     */
    protected function setExcelColumnCast($sheet, $key, $column, $firstRow = 2, $numOfRow = 100)
    {
        $cast = array_get($this->getExcelColumnCasts(), $key);
        if ($cast) {
            if ($cast == 'enum') {
                $source = $this->getEnumValues($key);
                for ($i = $firstRow; $i <= $numOfRow; $i++) {
                    $cellValidation = $sheet->getCell(sprintf('%s%s', $column, $i))->getDataValidation();
                    $this->setCellValidationList($cellValidation, $key, $source);
                }
            } else {
                $format = $this->getExcelCastFormat($cast);

                $sheet->setColumnFormat([
                    $column => $format
                ]);
            }
        }
    }

    protected function getExcelColumnCasts()
    {
        return [];
    }

    protected function setCellValidationList(PHPExcel_Cell_DataValidation $cellValidation, $key, $source = [])
    {
        $name = array_get($this->getExcelHeaders(), $key);
        $list = implode(',', $source);

        $cellValidation->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
        $this->setSellValidationErrorMessage($cellValidation, $name);
        $cellValidation->setPromptTitle("请从 [$list] 中选择数据");
        $cellValidation->setFormula1('"' . $list . '"');
    }

    /**
     * @param PHPExcel_Cell_DataValidation $cellValidation
     * @param $name
     */
    protected function setSellValidationErrorMessage(PHPExcel_Cell_DataValidation $cellValidation, $name): void
    {
        $cellValidation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
        $cellValidation->setAllowBlank(false);
        $cellValidation->setShowInputMessage(true);
        $cellValidation->setShowErrorMessage(true);
        $cellValidation->setShowDropDown(true);
        $cellValidation->setErrorTitle('输入数据不正确');
        $cellValidation->setError("[$name] 的输入数据不正确");
    }
    /**
     * @return string
     */
    public function getExcelType()
    {
        return 'xlsx';
    }

    public function getExcelHeaders()
    {
        return [];
    }

    protected function shouldCacheTemplate()
    {
        return true;
    }

}
