1、Laravel使用redis哨兵模式（适用于predis）
------------------------
修改config/database.php文件的“Redis Databases”部分代码，具体内容示例如下：
```php
'redis' => [
         'client' => 'predis',
         'default' => [
            'tcp://10.1.1.232:17001?timeout=0.100',
            'tcp://10.1.1.232:17002?timeout=0.100',
            'tcp://10.1.1.232:17003?timeout=0.100',
        ],
        'options' => [
            'replication' => 'sentinel',
            'service' => 'mymaster',
            'parameters' => [
                'password' => 'wtf',
                'database' => 0,
            ],
            'prefix' => 'blog'
        ]
    ]
```
配置说明：    
①default数组配置sentinel节点的访问地址，可多个    
②options的replication为sentinel，不可修改    
③options的service为redis sentinel的服务名（即参考文件②里的master-group-name），用于自动发现节点的    
④options的parameters里的password，用于设置redis的访问密码，可选    
⑤options的parameters里的database，用于指定redis使用的数据库，默认为0，值范围：0-15，可选    
⑥options的prefix，用于设置键前缀，可选    


2、代码参考
--------------------

vendor/laravel/framework/src/Illuminate/Redis/RedisManager.php  connection    
vendor/laravel/framework/src/Illuminate/Redis/Connectors/PredisConnector.php connect    
vendor/predis/predis/src/Client.php createConnection    
bass/vendor/predis/predis/src/Connection/Aggregate/SentinelReplication.php    

3、参考文件
----------------------
①https://github.com/nrk/predis#replication    
②https://redis.io/topics/sentinel    
