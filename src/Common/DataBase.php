<?php

namespace App\Common;

class DataBase
{
    private const PATH = __DIR__ . "/../../data/database.json";

    public function getData($table = null)
    {
        $get = file_get_contents(self::PATH);
        $arrData = json_decode($get, true);
        if ($table != null && array_key_exists($table, $arrData)) {
            return $arrData[$table];
        } else {
            return $arrData;
        }
    }

    public function setData($data, $table = null, $id = null)
    {
        $arrData = $this->getData();

        if ($table != null) {
            if ($id != null) {
                foreach ($arrData[$table] as $key=>$element) {
                    if ($element['id'] == $id) {
                        $arrData[$table][$key] = $data;
                    }
                }
            } else {
                $arrData[$table] = $data;
            }
        } else {
            $arrData = $data;
        }

        $json = json_encode($arrData, JSON_PRETTY_PRINT);
        return file_put_contents(self::PATH, $json);
    }
}
