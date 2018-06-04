一、Controller


二、Model

    public function initialize(){
        $this->useDynamicUpdate($dynamicUpdate);//是否只更新变化字段（字段更新被覆盖/还原）
        self::setup(array('notNullValidations'=>false));//不进行null检测（解决空字符串被认为是null问题）
    }
三、View
