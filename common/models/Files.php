<?php

namespace common\models;

use Yii;
use yii\helpers\Url;
use yii\web\UploadedFile;

/**
 * This is the model class for table "files".
 *
 * @property integer $id
 * @property string $filename
 * @property string $filepath
 * @property string $fileurl
 * @property string $filemime
 * @property integer $filesize
 * @property string $create_time
 * @property integer $create_user_id
 *
 */
class Files extends \common\models\mainclass\FSMCreateModel
{

    public $file;
    public $uploadedFile;
    public $oldFileName;

    private $uploadUrl;
    private $baseUrl;

    public function init()
    {
        $baseUrl = Url::base(true);
        $this->baseUrl = str_replace('/backend', '', $baseUrl);
        $this->uploadUrl = $this->baseUrl . '/uploads/';
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'files';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            //[['filename', 'filepath', 'fileurl', 'filemime', 'filesize'], 'required'],
            [['filesize', 'create_user_id'], 'integer'],
            [['file', 'create_time'], 'safe'],
            [['file'], 'file', 'extensions' => 'pdf, png, jpg, jpeg, doc, docx'],
            [['filename', 'fileurl', 'filepath', 'filemime'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function modelTitle($n = 1, $translate = true)
    {
        return parent::label('files', 'File|Files', $n);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'filename' => Yii::t('files', 'File name'),
            'filepath' => Yii::t('files', 'File path'),
            'fileurl' => Yii::t('files', 'File Url'),
            'filemime' => Yii::t('files', 'File Mime'),
            'filesize' => Yii::t('files', 'File size'),
            'create_time' => Yii::t('app', 'Create Time'),
            'create_user_id' => Yii::t('app', 'Create User'),
        ];
    }

    /**
     * fetch stored uploadedFile name with complete path 
     * @return string
     */
    public function getUploadedFilePath()
    {
        return !empty($this->filepath) ? $this->filepath : 
                (!empty($this->filename) ? (Yii::$app->params['uploadPath'] . $this->filename) : null);
    }

    /**
     * fetch stored uploadedFile url
     * @return string
     */
    public function getUploadedFileUrl()
    {
        return !empty($this->fileurl) ? $this->fileurl : 
                (!empty($this->filename) ? $this->uploadUrl . $this->filename : null);
    }

    /**
     * Process upload of file
     *
     * @return mixed the uploaded file instance
     */
    public function uploadFile($destinPath = '')
    {
        if (!empty($destinPath)) {
            if (!in_array(substr("$destinPath", -1), ['/', '\\'])) {
                $destinPath .= '/';
            }
        }
        // get the uploaded file instance. for multiple file uploads
        // the following data will return an array (you may need to use
        // getInstances method)
        $this->uploadedFile = UploadedFile::getInstance($this, 'filename');

        // if no file was uploaded abort the upload
        if (empty($this->uploadedFile)) {
            return false;
        }   

        $filename = $this->uploadedFile->name;
        $array = explode('.', $filename);
        $ext = end($array);
        // generate a unique file name
        $newFilename = Yii::$app->security->generateRandomString() . ".{$ext}";
        // the path to save file, you can set an uploadPath
        // in Yii::$app->params (as used in example below)
        $this->filename = $filename;
        $this->filepath = Yii::$app->params['uploadPath'] . $destinPath . $newFilename;
        $this->fileurl = $this->uploadUrl . $destinPath . $newFilename;
        $this->filemime = $this->uploadedFile->type;
        $this->filesize = $this->uploadedFile->size;

        // the uploaded file instance
        return $this->uploadedFile;
    }

    public function save($runValidation = true, $attributeNames = NULL)
    {
        $result = true;
        if(!empty($this->oldFileName)){
            $this->deleteOldFile();
        }
        
        if (!is_null($this->uploadedFile)) {
            $dir = dirname($this->filepath);
            if(!is_dir($dir)){
                mkdir($dir, 0777, true);
            }
            
            $result = $this->uploadedFile->saveAs($this->filepath);
        }
        return $result && parent::save();
    }

    public function saveDataToFile($filename, $data, $destinPath = '')
    {
        if (!empty($destinPath)) {
            if (!in_array(substr("$destinPath", -1), ['/', '\\'])) {
                $destinPath .= '/';
            }
        }

        $array = explode('.', $filename);
        $ext = end($array);

        $this->filename = $filename;
        $this->filepath = str_replace('\\', '/', Yii::$app->params['uploadPath'] . $destinPath . $filename);
        $this->fileurl = $this->uploadUrl . $destinPath . $filename;
        
        $dir = dirname($this->filepath);
        if(!is_dir($dir)){
            mkdir($dir, 0777, true);
        }
        if(!file_put_contents($this->filepath, $data)){
            return;
        }
        
        $this->filemime = mime_content_type($this->filepath);
        $this->filesize = filesize($this->filepath);
        
        return $this->save();
    }
    
    /**
     * Process deletion of file
     *
     * @return boolean the status of deletion
     */
    public function deleteFile($file = null)
    {
        $file = isset($file) ? $file : $this->uploadedFilePath;

        // check if file exists on server
        if (!empty($file) && file_exists($file)) {
            // check if uploaded file can be deleted on server
            if (!unlink($file)) {
                return false;
            }
        }

        $dir = dirname($file);
        if (is_dir($dir) && (count(glob("$dir/*")) === 0) && !rmdir($dir)) {
            return false;
        }

        // if deletion successful, reset your file attributes
        $this->filename = $this->filepath = $this->filemime = $this->filesize = null;

        return true;
    }
    
    public function deleteOldFile($file = null)
    {
        $file = isset($file) ? $file : $this->oldFileName;

        // check if file exists on server
        if (!empty($file) && file_exists($file)) {
            // check if uploaded file can be deleted on server
            if (!unlink($file)) {
                return false;
            }
        }

        $dir = dirname($file);
        if (is_dir($dir) && (count(glob("$dir/*")) === 0) && !rmdir($dir)) {
            return false;
        }

        return true;        
    }

    public function delete()
    {
        if ($this->deleteFile()) {
            return parent::delete();
        } else {
            return;
        }
    }

}
