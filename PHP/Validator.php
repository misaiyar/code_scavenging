<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/28
 * Time: 15:24
 * @link vendor/laravel/framework/src/Illuminate/Validation/Concerns/ValidatesAttributes.php
 */

namespace App\Utility;


class Validator{

    /**
     * The validation rules that imply the field is required.
     *
     * @var array
     */
    protected $implicitRules = [
        'Required', 'Filled', 'RequiredWith', 'RequiredWithAll', 'RequiredWithout',
        'RequiredWithoutAll', 'RequiredIf', 'RequiredUnless', 'Accepted', 'Present',
    ];

    /**
     * The validation rules which depend on other fields as parameters.
     *
     * @var array
     */
    protected $dependentRules = [
        'RequiredWith', 'RequiredWithAll', 'RequiredWithout', 'RequiredWithoutAll',
        'RequiredIf', 'RequiredUnless', 'Confirmed', 'Same', 'Different', 'Unique',
        'Before', 'After', 'BeforeOrEqual', 'AfterOrEqual', 'Gt', 'Lt', 'Gte', 'Lte',
    ];

    /**
     * The size related validation rules.
     *
     * @var array
     */
    protected $sizeRules = ['Size', 'Between', 'Min', 'Max', 'Gt', 'Lt', 'Gte', 'Lte'];

    /**
     * The numeric related validation rules.
     *
     * @var array
     */
    protected $numericRules = ['Numeric', 'Integer'];

    //market/vendor/caouecs/laravel-lang/src/zh-CN/validation.php
    protected $error_msg = [
        '__' => ':attribute 校验不通过',
        'after'                => ':attribute 必须要晚于 :date。',
        'after_or_equal'       => ':attribute 必须要等于 :date 或更晚。',
        'alpha'                => ':attribute 只能由字母组成。',
        'alpha_dash'           => ':attribute 只能由字母、数字、短划线(-)和下划线(_)组成。',
        'alpha_num'            => ':attribute 只能由字母和数字组成。',
        'before'               => ':attribute 必须要早于 :date。',
        'before_or_equal'      => ':attribute 必须要等于 :date 或更早。',
        'between'              => [
            'numeric' => ':attribute 必须介于 :min - :max 之间。',
            'file'    => ':attribute 必须介于 :min - :max KB 之间。',
            'string'  => ':attribute 必须介于 :min - :max 个字符之间。',
            'array'   => ':attribute 必须只有 :min - :max 个单元。',
        ],
        'confirmed'            => ':attribute 两次输入不一致。',
        'date'                 => ':attribute 不是一个有效的日期。',
        'date_equals'          => ':attribute 必须要等于 :date。',
        'date_format'          => ':attribute 的格式必须为 :format。',
        'email'                => ':attribute 不是一个合法的邮箱。',
        'filled'               => ':attribute 不能为空。',
        'integer'              => ':attribute 必须是整数。',
        'ip'                   => ':attribute 必须是有效的 IP 地址。',
        'ipv4'                 => ':attribute 必须是有效的 IPv4 地址。',
        'ipv6'                 => ':attribute 必须是有效的 IPv6 地址。',
        'json'                 => ':attribute 必须是正确的 JSON 格式。',
        'max'                  => [
            'numeric' => ':attribute 不能大于 :max。',
            'file'    => ':attribute 不能大于 :max KB。',
            'string'  => ':attribute 不能大于 :max 个字符。',
            'array'   => ':attribute 最多只有 :max 个单元。',
        ],
        'min'                  => [
            'numeric' => ':attribute 必须大于等于 :min。',
            'file'    => ':attribute 大小不能小于 :min KB。',
            'string'  => ':attribute 至少为 :min 个字符。',
            'array'   => ':attribute 至少有 :min 个单元。',
        ],
        'size'                 => [
            'numeric' => ':attribute 大小必须为 :size。',
            'file'    => ':attribute 大小必须为 :size KB。',
            'string'  => ':attribute 必须是 :size 个字符。',
            'array'   => ':attribute 必须为 :size 个单元。',
        ],
        'numeric'              => ':attribute 必须是一个数字。',
        'regex'                => ':attribute 格式不正确。',
        'required'             => ':attribute 不能为空。',
        'url'                  => ':attribute 格式不正确。',
        'uuid'                 => ':attribute 必须是有效的 UUID。',
    ];


    protected static $instance = null;

    private $current_rules = [], $current_data = [], $current_error = [],$current_validated = [],$current_key=null,$current_rule=null;
    public $current_args=null;

    private function __construct(){}

    public function init($data,$rules,$msg){
        $this->current_data = $data;
        $this->current_rules = $rules;
        $this->current_error = [];
        $this->current_validated = [];
        $this->current_key = $this->current_rule = $this->current_args = null;
        $this->error_msg = array_merge($this->error_msg,$msg);
    }

    public static function make(array $data,array $rules,$msg=[]){
        if( !self::$instance ){
            self::$instance = new self();
        }
        self::$instance->init($data,$rules,$msg);
        return self::$instance;
    }

    public function fails(){
        return $this->passed();
    }

    public function failed(){
        return $this->current_error;
    }

    public function passed(){
        if( empty($this->current_rules) ){
            return $this->current_error;
        }
        foreach ($this->current_rules as $_key=>$_rule){
            $this->current_key = $_key;
            if( is_array($_rule) ){
                $this->validateArrayAttribute($_key,$_rule);
            }else {
                $this->validateStringAttribute($_key, $_rule);
            }
        }
        return $this->current_error;
    }

    public function validated(){
        return $this->current_validated;
    }

    private function validateArrayAttribute($key,$rule_arr){
        $value = isset($this->current_data[$key]) ? trim($this->current_data[$key]) : NULL;
        foreach ($rule_arr as $_key=>$rule){
            if( empty($rule) ){
                continue;
            }
            $this->current_rule = $_key;
            if($rule instanceof \Closure){
                $result = $rule($key,$value,$this);
            }else{
                $result = $this->_validateAttribute($key,$value,$rule);
            }
            if(!$result){
                $this->current_error[$key] = $this->getLastError( $value );
                return false;
            }
        }
        if( !isset($this->current_validated[$key]) && isset($this->current_data[$key]) ){
            $this->current_validated[$key] = $value;
        }
        return true;
    }

    private function validateStringAttribute($key,$rule_str){
        $value = isset($this->current_data[$key]) ? trim($this->current_data[$key]) : NULL;
        $rules = explode('|',$rule_str);//竖线的实体编号是：&#124;
        foreach ($rules as $rule){
            $rule = trim($rule);
            if( empty($rule) ){
                continue;
            }
            $result = $this->_validateAttribute($key,$value,$rule);
            if( $result!==true ){
                $this->current_error[$key] = $this->getLastError( $value );;
                return false;
            }
        }
        if( !isset($this->current_validated[$key]) && isset($this->current_data[$key]) ){
            $this->current_validated[$key] = $value;
        }
        return true;

    }

    private function _validateAttribute($key,$value,$rule){
        if(( $pos = strpos($rule,':'))!==false){
            $this->current_rule = substr($rule,0,$pos);
            $method = 'rule'.str_replace('_', '', ucwords(substr($rule,0,$pos),'_'));
            $_arg_str = substr($rule,$pos+1);
            $args = explode(',',$_arg_str);//逗号的实体编号是：&#44; 竖线的实体编号是：&#124;
            if( count($args)>=2 ) {
                array_walk($args, function (&$item) {
                    $item = html_entity_decode($item);
                });
            }
            $this->current_args = $args;
            $result = $this->$method(isset($this->current_data[$key]),$value,...$args );
        }else{
            $this->current_rule = $rule;
            $method = 'rule'.str_replace('_', '', ucwords($rule,'_'));
            $result = $this->$method(isset($this->current_data[$key]),$value);
        }
        return $result;
    }

    /**  规则处理函数 START **/

    private function ruleRequiredWith($key_exist, $value,...$args){}
    private function ruleRequiredWithAll(){}
    private function ruleRequiredWithout(){}

    private function ruleRequiredWithoutAll(){}
    private function ruleRequiredIf(){}
    private function ruleRequiredUnless(){}
    private function ruleAccepted(){}
    private function rulePresent(){}
    private function ruleSame(){}
    private function ruleDifferent(){}
    private function ruleUnique(){}

    private function ruleGt(){}
    private function ruleLt(){}
    private function ruleGte(){}
    private function ruleLte(){}

    private function ruleRequired($key_exist, $value){
        return $key_exist && ( is_numeric($value) && $value==0 || !empty($value));
    }

    private function ruleFilled($key_exist, $value){
        return !$key_exist || !empty($value);
    }

    private function ruleConfirmed($key_exist, $value){
        if( !$key_exist ){
            return true;
        }
        $_key = $this->current_key.'_confirmed';
        if( !isset( $this->current_data[$_key] ) ){
            return false;
        }
        $_c_value = trim( $this->current_data[$_key] );
        return  !empty($_c_value) && $_c_value == $value;
    }

    private function ruleDate($key_exist, $value){
        if(!$key_exist){
            return true;
        }
        $time = strtotime($value);
        if( $time>0 ){
            return true;
        }
        return false;
    }

    private function ruleDateEquals($key_exist, $value,$base){
        $this->current_args = ['date'=>$base];
        return !$key_exist || $this->_dateCompare($value,$base);
    }

    private function ruleBefore($key_exist, $value,$base){
        $this->current_args = ['date'=>$base];
        return !$key_exist || $this->_dateCompare($value,$base,'<');
    }

    private function ruleAfter($key_exist, $value,$base){
        $this->current_args = ['date'=>$base];
        return !$key_exist || $this->_dateCompare($value,$base,'>');
    }

    private function ruleBeforeOrEqual($key_exist, $value,$base){
        $this->current_args = ['date'=>$base];
        return !$key_exist || $this->_dateCompare($value,$base,'<=');
    }

    private function ruleAfterOrEqual($key_exist, $value,$base){
        $this->current_args = ['date'=>$base];
        return !$key_exist || $this->_dateCompare($value,$base,'>=');
    }

    private function ruleDateFormat($key_exist, $value,$format){
        $this->current_args = ['format'=>$format];
        return !$key_exist || date($format,strtotime($value)) == $value;
    }

    private function ruleSize($key_exist, $value,$size){
        $this->current_args = ['size'=>$size];
        return !$key_exist || $this->size($value)==$size;
    }
    private function ruleBetween($key_exist, $value,$min,$max){
        $this->current_args = ['min'=>$min,'max'=>$max];
        return !$key_exist || $this->size($value) >=$min && $this->size($value) <=$max;
    }
    private function ruleMin($key_exist, $value,$min){
        $this->current_args = ['min'=>$min];
        return !$key_exist || $this->size($value) >=$min;
    }
    private function ruleMax($key_exist, $value,$max){
        $this->current_args = ['max'=>$max];
        return !$key_exist || $this->size($value) <=$max;
    }
    private function ruleNumeric($key_exist, $value ){
        return !$key_exist || is_numeric($value);
    }
    private function ruleInteger($key_exist, $value ){
        return !$key_exist || intval($value)==$value;
    }

    private function ruleRegex($key_exist, $value,$regex){
        return !$key_exist || preg_match($regex,$value);
    }

    private function ruleAlpha($key_exist, $value){
        return !$key_exist || preg_match('/^[a-zA-Z]+$/',$value);
    }

    private function ruleAlphaDash($key_exist, $value){
        return !$key_exist || preg_match('/^[a-zA-Z0-9\-\_]+$/',$value);
    }

    private function ruleAlphaNum($key_exist, $value){
        return !$key_exist || preg_match('/^[a-zA-Z0-9]+$/',$value);
    }

    private function ruleEmail($key_exist, $value){
        return !$key_exist || filter_var($value, FILTER_VALIDATE_EMAIL)!==false;
    }

    private function ruleIp($key_exist, $value){
        return !$key_exist || filter_var($value, FILTER_VALIDATE_IP)!==false;
    }

    private function ruleIpv4($key_exist, $value){
        return !$key_exist || filter_var($value, FILTER_VALIDATE_IP,array('flags' => FILTER_FLAG_IPV4))!==false;
    }

    private function ruleIpv6($key_exist, $value){
        return !$key_exist || filter_var($value, FILTER_VALIDATE_IP,array('flags' => FILTER_FLAG_IPV6))!==false;
    }

    private function ruleJson2array($key_exist, $value){
        if(!$key_exist){
            return true;
        }
        $_value = json_decode($value,true);
        if( is_array($_value) ){
            $this->current_validated[$this->current_key] = $_value;
            return true;
        }
        return false;
    }

    private function ruleJson($key_exist, $value){
        return !$key_exist || is_array(json_decode($value,true));
    }

    private function ruleUrl($key_exist, $value){
        return !$key_exist || filter_var($value, FILTER_VALIDATE_URL)!==false;
    }

    private function ruleUuid($key_exist, $value){
        return !$key_exist || preg_match('/^[\da-f]{8}-[\da-f]{4}-[\da-f]{4}-[\da-f]{4}-[\da-f]{12}$/iD', $value);
    }

    /**  规则处理函数 END **/

    private function _dateCompare($value,$base,$op='='){
        $time = strtotime($value);
        if( $time===false || $time<=0 ){
            return false;
        }
        $_compare = strtotime( isset($this->current_data[$base])? $this->current_data[$base] : $base );
        switch ($op){
            case '>=':
                $result = $time >= $_compare;
                break;
            case '>':
                $result = $time > $_compare;
                break;
            case '<=':
                $result = $time <= $_compare;
                break;
            case '<':
                $result = $time < $_compare;
                break;
            default:
                $result = $time == $_compare;
                break;
        }
        return $result;
    }

    private function is_realnumber($value){
        return is_numeric($value) && $value <= PHP_INT_MAX && $value >= - PHP_INT_MAX -1 ;
    }

    private function size($value){
        if( is_array($value) ){
            return count($value);
        }else if( $this->is_realnumber($value) && $this->hasRule($this->numericRules) ){
            return $value + 0;
        }else{
            return mb_strlen($value);
        }
        return false;
    }

    private function getValueType($value){
        if( is_file( $value ) ){
            return 'file';
        }else if( is_array($value) ){
            return 'array';
        }else if( $this->is_realnumber($value) ){
            return 'numeric';
        }
        return 'string';
    }

    private function getLastError( $value ){
        $_key = isset( $this->error_msg[ $this->current_key.'.'.$this->current_rule ] ) ? $this->current_key.'.'.$this->current_rule :
            ( isset( $this->error_msg[ $this->current_rule ] ) ? $this->current_rule :'__' );
        $msg = $this->error_msg[ $_key ];
        if( is_array($msg) ){
            $msg = $msg[ $this->getValueType($value) ];
        }
        $search = [':attribute'];
        $replace = [$this->current_key];
        foreach ( $this->current_args as $_key=>$_value ){
            $search[] = ':'.$_key;
            $replace[] = $_value?:'';
        }
        return str_replace($search, $replace, $msg);
    }

    private function hasRule( $rules ){
        $_rules = $this->current_rules[$this->current_key];
        if( is_array($_rules) ){
            $mix = array_intersect($_rules,$rules);
            return !empty($mix);
        }else{
            return preg_match('/(^|\|)\s*('.implode('|',(array)$rules).')\s*(:|$|\|)/is',$_rules);
        }
    }
}
