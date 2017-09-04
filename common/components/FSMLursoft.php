<?php

namespace common\components;

use Yii;
use yii\base\Object;
use yii\helpers\ArrayHelper;
use stdClass;
use Exception;
use SimpleXMLElement;

class FSMLursoft extends Object
{

    const LURSOFT_USER_ID = 'ecf_xml';
    const LURSOFT_PASSWORD = 'spring_time';
    const LURSOFT_URL = 'https://www.lursoft.lv/server3';
    const LURSOFT_LOGIN = 'LOGINXML';
    const LURSOFT_LOGOUT = 'LOGOUTXML';
    const LURSOFT_SEARCH = 'URPERSON_XML';

    private $sessionId;

    private function _request(array $request)
    {
        $request = http_build_query($request);
        $url = $this::LURSOFT_URL . '?' . $request;
        if (($xmlstr = file_get_contents($url)) === false) {
            //return ['result' => false, 'message' => 'Error fetching XML'];
            throw new Exception('Error fetching XML');
        } else {
            $clean_xml = str_ireplace(['soap:', 'Lursoft:'], '', $xmlstr);
            libxml_use_internal_errors(true);
            $data = simplexml_load_string($clean_xml);
            if (!$data) {
                throw new Exception('Error loading XML');
                /*
                  $message = [];
                  $message[] = "Error loading XML\n";
                  //echo "Error loading XML\n";
                  foreach (libxml_get_errors() as $error) {
                  $message[] = "\t".$error->message;
                  //echo "\t", $error->message;
                  }
                  return ['result' => false, 'message' => $message];
                 * 
                 */
            } else {
                return $data;
            }
        }
    }

    private function _login()
    {
        $request = [
            'act' => $this::LURSOFT_LOGIN,
            'Userid' => $this::LURSOFT_USER_ID,
            'Password' => $this::LURSOFT_PASSWORD
        ];
        if (($response = $this->_request($request)) && isset($response->Header) && isset($response->Header->SessionId)) {
            $this->sessionId = (string) $response->Header->SessionId;
            return true;
        }
        return false;
    }

    private function _logout()
    {
        $request = [
            'act' => $this::LURSOFT_LOGOUT,
            'SessionId' => $this->sessionId,
        ];
        if (($response = $this->_request($request)) && isset($response->Header) && isset($response->Header->SessionId)) {
            return true;
        }
        return false;
    }

    private function _search($params = [])
    {
        $request = [
            'act' => $this::LURSOFT_SEARCH,
            'SessionId' => $this->sessionId,
            'name' => !empty($params['name']) ? $params['name'] : 'aaa',
            'code' => !empty($params['code']) ? $params['code'] : null,
            'userperscode' => '123',
            'part' => 'KAS',
            'utf' => 1,
        ];
        if (($response = $this->_request($request)) && isset($response->Body) && isset($response->Body->answer)) {
            $answer = $response->Body->answer;
            return $answer;
        }
        return false;
    }

    public function search($params)
    {
        if ($this->_login()) {
            if ($data = $this->_search($params)) {
                $this->_logout();
                return ['result' => true, 'answer' => $data];
            } else {
                $message = Yii::t('client', 'Can\'t to start procedure of searching.');
            }
        } else {
            $message = Yii::t('client', 'Can\'t to connect to Lursoft server.');
        }
        //Yii::$app->getSession()->setFlash('error', $message);
        Yii::error($message, __METHOD__);
        return ['result' => false, 'message' => $message];
    }

}
