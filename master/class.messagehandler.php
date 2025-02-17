<?php

////////////////////////////////////////////////////
// User - PHP messageHandler Class
//
// Class to handle msgs in php for NuForge Development, LLC
// 
// License: LGPL, see LICENSE
////////////////////////////////////////////////////

/**
 * @author nuForge
 */
class messageHandler
{

    private $myOutputType;
    private $myMessageStatusTypes;
    private $myMessages = array();
    private $iconBaseDirectory = '/images/icons/';

    function __construct($f_output_type = false, $f_default = false)
    {

        DEFINE('GENERATE_OUTPUT_METHOD_PREFIX', 'generateOutput_');
        DEFINE('GENERATE_OUTPUT_METHOD_DEFAULT_METHOD', 'generateOutput_default');

        DEFINE('MESSAGE_OUTPUT_TYPE_POPUP', 'popup');
        DEFINE('MESSAGE_OUTPUT_TYPE_FIELDSET', 'fieldset');
        DEFINE('MESSAGE_OUTPUT_TYPE_DEFAULT', 'fieldset');
        DEFINE('MESSAGE_OUTPUT_TYPE_BOOTSTRAP', 'bootstrap');

        DEFINE('MESSAGE_HOLDER_CLASS_NAME', 'message_holder');

        DEFINE('MESSAGE_STATUS_TYPE_PROPERTY_TITLE', 'title');
        DEFINE('MESSAGE_STATUS_TYPE_PROPERTY_ICON', 'icon');
        DEFINE('MESSAGE_STATUS_TYPE_PROPERTY_CLASS', 'class');

        DEFINE('MESSAGE_STATUS_TYPE_DEFAULT', 'default');
        DEFINE('MESSAGE_STATUS_TYPE_ERROR', 'error');
        DEFINE('MESSAGE_STATUS_TYPE_SUCCESS', 'success');
        DEFINE('MESSAGE_STATUS_TYPE_WARNING', 'warning');

        $this->myOutputType = ($f_output_type) ? $f_output_type : MESSAGE_OUTPUT_TYPE_FIELDSET;
        $f_default = ($f_default) ? $f_default : MESSAGE_STATUS_TYPE_ERROR;

        $this->myMessageStatusTypes[MESSAGE_STATUS_TYPE_ERROR][MESSAGE_STATUS_TYPE_PROPERTY_TITLE] = 'Error';
        $this->myMessageStatusTypes[MESSAGE_STATUS_TYPE_ERROR][MESSAGE_STATUS_TYPE_PROPERTY_ICON] = 'exclamation.png';
        $this->myMessageStatusTypes[MESSAGE_STATUS_TYPE_ERROR][MESSAGE_STATUS_TYPE_PROPERTY_GLYPHICON] = 'exclamation-sign';
        $this->myMessageStatusTypes[MESSAGE_STATUS_TYPE_ERROR][MESSAGE_STATUS_TYPE_PROPERTY_BOOTSTRAP_CLASS] = 'danger';
        $this->myMessageStatusTypes[MESSAGE_STATUS_TYPE_ERROR][MESSAGE_STATUS_TYPE_PROPERTY_CLASS] = 'error_message';
        $this->myMessageStatusTypes[MESSAGE_STATUS_TYPE_SUCCESS][MESSAGE_STATUS_TYPE_PROPERTY_TITLE] = 'Success';
        $this->myMessageStatusTypes[MESSAGE_STATUS_TYPE_SUCCESS][MESSAGE_STATUS_TYPE_PROPERTY_ICON] = 'accept.png';
        $this->myMessageStatusTypes[MESSAGE_STATUS_TYPE_SUCCESS][MESSAGE_STATUS_TYPE_PROPERTY_GLYPHICON] = 'ok';
        $this->myMessageStatusTypes[MESSAGE_STATUS_TYPE_SUCCESS][MESSAGE_STATUS_TYPE_PROPERTY_BOOTSTRAP_CLASS] = 'success';
        $this->myMessageStatusTypes[MESSAGE_STATUS_TYPE_SUCCESS][MESSAGE_STATUS_TYPE_PROPERTY_CLASS] = 'success_message';
        $this->myMessageStatusTypes[MESSAGE_STATUS_TYPE_WARNING][MESSAGE_STATUS_TYPE_PROPERTY_TITLE] = 'Warning';
        $this->myMessageStatusTypes[MESSAGE_STATUS_TYPE_WARNING][MESSAGE_STATUS_TYPE_PROPERTY_ICON] = 'error.png';
        $this->myMessageStatusTypes[MESSAGE_STATUS_TYPE_WARNING][MESSAGE_STATUS_TYPE_PROPERTY_GLYPHICON] = 'warning-sign';
        $this->myMessageStatusTypes[MESSAGE_STATUS_TYPE_WARNING][MESSAGE_STATUS_TYPE_PROPERTY_BOOTSTRAP_CLASS] = 'warning';
        $this->myMessageStatusTypes[MESSAGE_STATUS_TYPE_WARNING][MESSAGE_STATUS_TYPE_PROPERTY_CLASS] = 'warning_message';

        $this->myMessageStatusTypes[MESSAGE_STATUS_TYPE_DEFAULT] = $this->myMessageStatusTypes[$f_default];
    }

    public function addStatusMessage($f_message, $f_message_status_type = MESSAGE_STATUS_TYPE_ERROR)
    {
        $this->myMessages[$f_message_status_type][] = $f_message;
    }

    public function mergeStatusMessages($fa_new_status_messages)
    {
        if (empty($fa_new_status_messages) || !is_array($fa_new_status_messages)) {
            return $this->myMessages;
        }
        foreach ($fa_new_status_messages as $type => $messages) {
            $this->myMessages[$type] = (empty($this->myMessages[$type])) ? $messages : array_merge($this->myMessages[$type], $messages);
        }
        return $this->myMessages;
    }

    public function countStatusMessages($f_message_status_type)
    {
        return count($this->myMessages[$f_message_status_type]);
    }

    public function checkHasStatusMessages($f_message_status_type = false)
    {
        return ($f_message_status_type) ? isset($this->myMessages[$f_message_status_type]) : isset($this->myMessages);
    }

    public function generateOutput()
    {
        if (empty($this->myMessages)) {
            return false;
        }
        foreach ($this->myMessages as $messageStatusType => $messages) {
            $messageStatusType = (isset($this->myMessageStatusTypes[$messageStatusType])) ? $messageStatusType : MESSAGE_STATUS_TYPE_DEFAULT;
            if (method_exists($this, GENERATE_OUTPUT_METHOD_PREFIX . $this->myOutputType)) {
                $popupText .= call_user_func_array(array($this, GENERATE_OUTPUT_METHOD_PREFIX . $this->myOutputType), array($messages, $messageStatusType));
                //return call_user_func(array($this, 'generateOutput_' . $this->myOutputType, $messages, $messageType));
            } else {
                $popupText .= call_user_func_array(array($this, GENERATE_OUTPUT_METHOD_DEFAULT_METHOD), array($messages, $messageStatusType));
            }
        }

        return ($popupText) ? $popupText : false;
    }

    public function generateOutput_popup($fa_messages, $f_message_status_type)
    {
        $popupText = '<h2>' . $this->generateIconSource($f_message_status_type) . ' ' . $this->myMessageStatusTypes[$f_message_status_type][MESSAGE_STATUS_TYPE_PROPERTY_TITLE] . '</h2>';
        $popupText .= '<ul>';
        foreach ($fa_messages as $message) {
            $popupText .= '<li>' . $message . '</li>' . "\n";
        }
        $popupText .= '</ul>';

        return $popupText;
    }

    public function generateOutput_fieldset($fa_messages, $f_message_status_type)
    {

        $popupText = '<fieldset class="' . MESSAGE_HOLDER_CLASS_NAME . ' ' . $this->myMessageStatusTypes[$f_message_status_type][MESSAGE_STATUS_TYPE_PROPERTY_CLASS] . '" >';
        $popupText .= '<legend>' . $this->generateIconSource($f_message_status_type) . ' ' . $this->myMessageStatusTypes[$f_message_status_type][MESSAGE_STATUS_TYPE_PROPERTY_TITLE] . '</legend>';
        $popupText .= '<ul>';
        foreach ($fa_messages as $message) {
            $popupText .= '<li>' . $message . '</li>' . "\n";
        }
        $popupText .= '</ul>';
        $popupText .= '</fieldset>';

        return $popupText;
    }

    public function generateOutput_default($fa_messages, $f_message_status_type)
    {
        $popupText = '<' . $this->myOutputType . ' class="' . MESSAGE_HOLDER_CLASS_NAME . ' ' . $this->myMessageStatusTypes[$f_message_status_type][MESSAGE_STATUS_TYPE_PROPERTY_CLASS] . '" >';
        $popupText .= '<ul>';
        foreach ($fa_messages as $message) {
            $popupText .= '<li>' . $message . '</li>' . "\n";
        }
        $popupText .= '</ul>';
        $popupText .= '</' . $this->myOutputType . '>';
        return $popupText;
    }
    public function generateOutput_bootstrap($fa_messages, $f_message_status_type)
    {
        $popupText = '<div class="panel panel-' . $this->myMessageStatusTypes[$f_message_status_type][MESSAGE_STATUS_TYPE_PROPERTY_BOOTSTRAP_CLASS] . '" >';
        $popupText .= '<div class="panel-heading"><span class="glyphicon glyphicon-' . $this->myMessageStatusTypes[$f_message_status_type][MESSAGE_STATUS_TYPE_PROPERTY_GLYPHICON] . '"></span> ' . $this->myMessageStatusTypes[$f_message_status_type][MESSAGE_STATUS_TYPE_PROPERTY_TITLE] . '</div>';
        $popupText .= '<ul class="list-group">';
        foreach ($fa_messages as $message) {
            $popupText .= '<li class="list-group-item small">' . $message . '</li>' . "\n";
        }
        $popupText .= '</ul>';
        $popupText .= '</div>';
        return $popupText;
    }

    public function output($f_message_status_type = false)
    {

        if (empty($f_message_status_type)) {
            return $this->generateOutput();
        }
        if (!empty($this->myMessages[$f_message_status_type])) {
            switch ($this->myOutputType) {

                case MESSAGE_OUTPUT_TYPE_FIELDSET:

                    $popupText = $this->generateOutput_fieldset($this->myMessages[$f_message_status_type], $f_message_status_type);

                    break;

                case MESSAGE_OUTPUT_TYPE_POPUP:

                    $popupText = $this->generateOutput_popup($this->myMessages[$f_message_status_type], $f_message_status_type);

                    break;
                case MESSAGE_OUTPUT_TYPE_BOOTSTRAP:
                    $popupText = $this->generateOutput_bootstrap($this->myMessages[$f_message_status_type], $f_message_status_type);

                    break;
                default:
                    $popupText = $this->generateOutput_default($this->myMessages[$f_message_status_type], $f_message_status_type);
                    break;
            }
            return $popupText;
        } else {
            return false;
        }
    }

    public function generateIconSource($f_message_status_type)
    {
        $img_src = $this->iconBaseDirectory . $this->myMessageStatusTypes[$f_message_status_type][MESSAGE_STATUS_TYPE_PROPERTY_ICON];
        $image = '<img src="' . $img_src . '" width="16" height="16" align="absmiddle" border="0" title="' . $this->myMessageStatusTypes[$f_message_status_type][MESSAGE_STATUS_TYPE_PROPERTY_TITLE] . '" alt="' . $this->myMessageStatusTypes[$f_message_status_type][MESSAGE_STATUS_TYPE_PROPERTY_TITLE] . '" /> ';
        return (file_exists($_SERVER['DOCUMENT_ROOT'] . $img_src)) ? $image : false;
    }

    public function setIconBaseDirectory($f_icon_base_directory)
    {
        $this->iconBaseDirectory = $f_icon_base_directory;
    }

    public function getIconBaseDirectory($f_icon_base_directory)
    {
        return $this->iconBaseDirectory;
    }
    public function setOutputType($f_output_type)
    {
        $this->myOutputType = $f_output_type;
    }

    public function getOutputType()
    {
        return $this->myOutputType;
    }

    public function setMyMessages($fa_my_messages)
    {
        $this->myMessages = $fa_my_messages;
    }

    public function getMyMessages()
    {
        return $this->myMessages;
    }

    // OLD FUNCTIONS

    public function add($f_message_status_type, $f_message = true)
    {
        $this->addStatusMessage($f_message, $f_message_status_type);
    }

    public function num($f_message_status_type)
    {
        return $this->countStatusMessages($f_message_status_type);
    }

    public function status($f_status)
    {
        return (isset($this->myMessages[$f_status]));
    }

    public function has_message($f_status_message = false)
    {

        return $this->checkHasStatusMessages($f_status_message);
    }

    public function merge($f_message) {}
}
