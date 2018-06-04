一、Controller


二、Model

1、NOT NULL及字段全更新问题        
    public function initialize(){
        $this->useDynamicUpdate($dynamicUpdate);//是否只更新变化字段（字段更新被覆盖/还原）
        self::setup(array('notNullValidations'=>false));//不进行null检测（解决空字符串被认为是null问题）
    }
    
2、sql字段莫名其妙被替换
  ①db连接的字符集问题，需在di注册时设置$connect->execute('set names utf8;')        
  ②框架问题        
  
三、View
