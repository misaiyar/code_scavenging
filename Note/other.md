1、服务器资源评估
----------------
要定一个标准，根据实际服务场景来做出评估，而不是拍脑袋做。这个评估有一个公式：设计数量=（PV/86400）×2/单机承载最大QPS/0.8。

2、计算UV
----------------
①使用bitmap精确统计：每个出现的uid对应的位置1，按位于或,进行计算
②使用HyperLogLog进行模糊计算
