<?php

namespace common\components;

use XMLWriter;

/**
 * Этот скрипт содержит класс, предназначенный для преобразования PHP массивов в
 * XML формат. Поддерживаются многомерные массивы.
 * 
 * Пример использования:
 * 
 *
 * @author Стаценко Владимир http://www.simplecoding.org <vova_33@gala.net>
 * @version 0.1
 */
class FSMArray2XML
{

    private $writer;
    private $version = '1.0';
    private $encoding = 'UTF-8';
    private $rootName = 'root';

    //конструктор
    function __construct()
    {
        $this->writer = new \XMLWriter();
    }

    /**
     * Преобразование PHP массива в XML формат.
     * Если исходный массив пуст, то XML файл будет содержать только корневой тег.
     *
     * @param $data - PHP массив
     * @return строка в XML формате
     */
    public function convert($data)
    {
        $this->writer->openMemory();
        $this->writer->startDocument($this->version, $this->encoding);
        if(!is_array($this->rootName)){
            $this->writer->startElement($this->rootName);
        }else{
            foreach ($this->rootName as $rootKey => $tagArr) {
                $tag = each($tagArr);
                $this->writer->startElement($rootKey);
                $this->writer->writeAttribute($tag['key'], $tag['value']);
                //$this->writer->writeCData($val);          
            }
        }
        if (is_array($data)) {
            $this->getXML($data);
        }
        $this->writer->endElement();
        return $this->writer->outputMemory();
    }

    /**
     * Установка версии XML
     *
     * @param $version - строка с номером версии
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * Установка кодировки
     *
     * @param $version - строка с названием кодировки
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }
   
    /**
     * Установка имени корневого тега
     *
     * @param $version - строка с названием корневого тега
     */
    public function setRootName($rootName)
    {
        $this->rootName = $rootName;
    }

    /*
     * Этот метод преобразует данные массива в XML строку.
     * Если массив многомерный, то метод вызывается рекурсивно.
     */
    private function getXML($data)
    {
        foreach ($data as $key => $val) {
            if (is_numeric($key)) {
                $this->getXML($val);
                continue;
            }
            if (is_array($val)) {
                if ($key == 'pic_url') {
                    foreach ($val as $path) {
                        $this->writer->writeElement($key, $path);
                    }
                } else {
                    $this->writer->startElement($key);
                    $this->getXML($val);
                    $this->writer->endElement();
                }
            } elseif (strpos($key, 'info') === false) {
                $this->writer->writeElement($key, $val);
            } else {
                $info = explode(' ', $key);
                $info = explode('=', $info[1]);
                $value = $info[1];
                $this->writer->startElement('info');
                $this->writer->writeAttribute('lang', $value);
                $this->writer->writeCData($val);
                $this->writer->endElement();
            }
        }
    }

}