<?php

class SimpleCaptcha
{

    private $recordId = '';
    protected $imageBin = '';
    protected $capString = '';
    protected $width = 120;
    protected $height = 50;
    protected $color;
    protected $bgColor;
    protected $defautColor = '000000';
    protected $defautBgColor = 'ffffff';

    public static function generate($options = array())
    {
        $recordId = self::getRecordId();

        return new SimpleCaptcha($recordId, $options);
    }

    public static function isValidAnswer($answer)
    {
        $recordId = self::getRecordId();
        $rightAnswer = $_SESSION['captcha'][$recordId];

        if (empty($rightAnswer)) {
            return false;
        }

        return (strcasecmp($answer, $rightAnswer) === 0);
    }

    public static function getAnswer()
    {
        $recordId = self::getRecordId();
        return $_SESSION['captcha'][$recordId];
    }

    private static function getRecordId()
    {
        $referer = $_SERVER['HTTP_REFERER'];
        $formId = (int) $_REQUEST['formID'];
        $formIndex = (int) $_REQUEST['formIndex'];

        return md5($_REQUEST['httpReferer'] . $_REQUEST['formID'] . $_REQUEST['formIndex']);
    }

    private function __construct($recordId, $options = array())
    {
        $this->recordId = $recordId;
        $this->initColors($options['color'], $options['bgColor']);
        $this->initCaptchaString();
        $this->storeAnswer();
        $this->initImageBin();
    }

    private function initColors($color, $bgColor)
    {
        $color = empty($color) ? $this->defautColor : $color;
        $bgColor = empty($bgColor) ? $this->defautBgColor : $bgColor;

        $this->color = Color::parseHexRgbString($color);
        $this->bgColor = Color::parseHexRgbString($bgColor);
        //Color::parseHexRgbString
    }

    private function storeAnswer()
    {
        if (!isset($_SESSION['captcha'])) {
            $_SESSION['captcha'] = array();
        }
        if (preg_match('/desyatka.com.ua/i', $_SERVER['HTTP_HOST']))
            $this->recordId = md5($_SERVER['HTTP_REFERER'] . $_REQUEST['formID'] . $_REQUEST['formIndex']);
            
        $_SESSION['captcha'][$this->recordId] = $this->capString;
    }

    protected function initCaptchaString()
    {
        $string = time();
        $string = substr(str_shuffle($string), 0, 4);
        $this->capString = $string;
    }

    protected function initImageBin()
    {
        $img = imagecreatetruecolor($this->width, $this->height);

        $bgColor = imagecolorallocate($img, $this->bgColor->getRedDec(), $this->bgColor->getGreenDec(), $this->bgColor->getBlueDec());
        imagefill($img, 0, 0, $bgColor);

        $textColor = imagecolorallocate($img, $this->color->getRedDec(), $this->color->getGreenDec(), $this->color->getBlueDec());
        $font = 'PressStart2P.ttf';
        $font_size = 18;

        imagettftext($img, $font_size, 0, 12, 40, $textColor, $font, $this->capString);

        ob_start();
        imagejpeg($img, null, 100);
        $this->imageBin = ob_get_contents(); // read from buffer
        ob_end_clean(); // delete buffer
        imagedestroy($img);
    }

    public function getImageBin()
    {
        return $this->imageBin;
    }

}

?>
