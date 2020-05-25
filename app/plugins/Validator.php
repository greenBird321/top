<?php
namespace MyApp\Plugins;

use MyApp\Models\Utils;

/**
 * 待完善
 * 自动验证使用示例：将验证规则配置成数组的形式,支持一维数组和二维数组
 * $rules = array(
 *                  array($age, 'isNum', '年龄必须是数字)，
 *                  array($mail, 'isEmail', '邮箱格式不正确')
 *             )
 * $this->validator->setRule($rules)->run();
 * 注意：如果验证方法的参数不止一个，则不一定适用于自动验证，请按需调用。
 */
class Validator
{

    protected $rules;

    public function setRule($rules = array())
    {
        if (is_array($rules) && !empty($rules)) {
            $this->rules = $rules;
            return $this;
        } else {
            Utils::tips('error', '无效的参数');
        }
    }


    public function run()
    {
        if (count($this->rules) == count($this->rules, 1)) {
            //一维数组处理逻辑
            $this->validate($this->rules);
        } else {
            //二维数组处理逻辑
            foreach ($this->rules as $rule) {
                $this->validate($rule);
            }
        }
    }


    /**
     * 调用方法验证 | $field待验证变量，$method验证方法，
     * @param $rule
     */
    public function validate($rule)
    {
        $field = $rule[0];
        $method = $rule[1];
        $msg = !empty($rule[2]) ? $rule[2] : "参数：{$field}验证失败";
        if (self::$method($field) === false) {
            Utils::tips('error', $msg);
        }
    }


    public static function __callStatic($methodName, $argument)
    {
        $errorTip = "验证方法 $methodName 不存在\n";
        Utils::tips('error', $errorTip);
    }

    /**
     * 是否为空值
     */
    public static function isEmpty($str)
    {
        $str = trim($str);
        return !empty($str) ? true : false;
    }

    /**
     * 浮点数验证
     */
    public static function isFloat($str)
    {
        if (!self::isEmpty($str)) return false;
        return ((string)(float)$str === (string)$str) ? true : false;
    }

    /**
     * 整数验证
     */
    public static function isInt($str)
    {
        if (!self::isEmpty($str)) return false;
        return ((string)(int)$str === (string)$str) ? true : false;
    }

    /**
     * 名称匹配，如用户名，目录名等
     * @param:string $str 要匹配的字符串
     * @param:$chinese 是否支持中文,默认支持，如果是匹配文件名，建议关闭此项（false）
     * @param:$charset 编码（默认utf-8,支持gb2312）
     */
    public static function isName($str, $chinese = true, $charset = 'utf-8')
    {
        if (!self::isEmpty($str)) return false;
        if ($chinese) {
            $match = (strtolower($charset) == 'gb2312') ? "/^[" . chr(0xa1) . "-" . chr(0xff) . "A-Za-z0-9_-]+$/" : "/^[x{4e00}-x{9fa5}A-Za-z0-9_]+$/u";
        } else {
            $match = '/^[A-za-z0-9_-]+$/';
        }
        return preg_match($match, $str) ? true : false;
    }

    /**
     * 邮箱验证
     */
    public static function isEmail($str)
    {
        if (!self::isEmpty($str)) return false;
        return preg_match("/([a-z0-9]*[-_\.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[\.][a-z]{2,3}([\.][a-z]{2})?/i", $str) ? true : false;
    }

    //手机号码验证
    public static function isMobile($str)
    {
        $exp = "/^13[0-9]{1}[0-9]{8}$|15[012356789]{1}[0-9]{8}$|18[012356789]{1}[0-9]{8}$|14[57]{1}[0-9]$/";
        if (preg_match($exp, $str)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * URL验证，纯网址格式，不支持IP验证
     */
    public static function isUrl($str)
    {
        if (!self::isEmpty($str)) return false;
        return preg_match('#(http|https|ftp|ftps)://([w-]+.)+[w-]+(/[w-./?%&=]*)?#i', $str) ? true : false;
    }

    /**
     * 验证中文
     * @param:string $str 要匹配的字符串
     * @param:$charset 编码（默认utf-8,支持gb2312）
     */
    public static function isChinese($str, $charset = 'utf-8')
    {
        if (!self::isEmpty($str)) return false;
        $match = (strtolower($charset) == 'gb2312') ? "/^[" . chr(0xa1) . "-" . chr(0xff) . "]+$/"
            : "/^[x{4e00}-x{9fa5}]+$/u";
        return preg_match($match, $str) ? true : false;
    }

    /**
     * UTF-8验证
     */
    public static function isUtf8($str)
    {
        if (!self::isEmpty($str)) return false;
        return (preg_match("/^([" . chr(228) . "-" . chr(233) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}){1}/", $word)
            == true || preg_match("/([" . chr(228) . "-" . chr(233) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}){1}$/", $word)
            == true || preg_match("/([" . chr(228) . "-" . chr(233) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}){2,}/", $word)
            == true) ? true : false;
    }

    /**
     * 验证长度
     * @param: string $str
     * @param: int $type(方式，默认min <= $str <= max)
     * @param: int $min,最小值;$max,最大值;
     * @param: string $charset 字符
     */
    public static function length($str, $type = 3, $min = 0, $max = 0, $charset = 'utf-8')
    {
        if (!self::isEmpty($str)) return false;
        $len = mb_strlen($str, $charset);
        switch ($type) {
            case 1: //只匹配最小值
                return ($len >= $min) ? true : false;
                break;
            case 2: //只匹配最大值
                return ($max >= $len) ? true : false;
                break;
            default: //min <= $str <= max
                return (($min <= $len) && ($len <= $max)) ? true : false;
        }
    }

    /**
     * 验证密码
     * @param string $value
     * @param int $length
     * @return boolean
     */
    public static function isPWD($value, $minLen = 6, $maxLen = 16)
    {
        $match = '/^[\\~!@#$%^&*()-_=+|{},.?\/:;\'\"\d\w]{' . $minLen . ',' . $maxLen . '}$/';
        $v = trim($value);
        if (empty($v))
            return false;
        return preg_match($match, $v);
    }

    /**
     * 验证用户名
     * @param string $value
     * @param int $length
     * @return boolean
     */
    public static function isNames($value, $minLen = 2, $maxLen = 16, $charset = 'ALL')
    {
        if (empty($value))
            return false;
        switch ($charset) {
            case 'EN':
                $match = '/^[_\w\d]{' . $minLen . ',' . $maxLen . '}$/iu';
                break;
            case 'CN':
                $match = '/^[_\x{4e00}-\x{9fa5}\d]{' . $minLen . ',' . $maxLen . '}$/iu';
                break;
            default:
                $match = '/^[_\w\d\x{4e00}-\x{9fa5}]{' . $minLen . ',' . $maxLen . '}$/iu';
        }
        return preg_match($match, $value);
    }


    /**
     * 验证时间
     * @param string $value
     */
    public static function checkTime($str)
    {
        return strtotime($str) ? true : false;
    }


}