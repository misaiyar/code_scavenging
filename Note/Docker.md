一、镜像
----------
1、列出镜像    
>     docker image ls [-a ::all]
>                     [-q ::ONLY ID]
>                     [-f before|since|label=xxx ::filter]
>                     [--format "{{.ID}}\t|: {{.Repository}}..."]
>                     [--digests]
>                     [image_name:tag]

2、镜像体积    
> docker system df        

3、删除无效镜像
> docker image prune    

4、镜像删除
> docker image rm [image ID] [image name] [image digests]

> docker image rm $(docker image ls -q redis)

5、从container定制镜像（不建议使用，黑箱镜像）    
> docker run --name webserver -d -p 80:80 nginx （基于基础镜像运行一个容器）    
docker exec -it webserver bash （进入容器，执行shell命令修改容器内内容）    
docker diff webserver （列出容器与初始容器的变更内容）    
docker commit [--author ::image maker] [--message ::change log] \<container ID> \<image name:tag> (从容器创建镜像)    
docker history \<image name:tag> （查看image变更历史）    

6、从Dockerfile 定制镜像      
- 指定基础镜像
> FROM [scratch](https://docs.docker.com/samples/library/scratch/ "A BASE IMAGE")
- 执行命令[RUN COPY ADD ]
>       RUN buildDeps='gcc libc6-dev make wget' \
>            && apt-get update \
>            && apt-get install -y $buildDeps \
>            && wget -O redis.tar.gz "http://download.redis.io/releases/redis-5.0.3.tar.gz" \
>            && mkdir -p /usr/src/redis \
>            && tar -xzf redis.tar.gz -C /usr/src/redis --strip-components=1 \
>            && make -C /usr/src/redis \
>            && make -C /usr/src/redis install \
>            && rm -rf /var/lib/apt/lists/* \
>            && rm redis.tar.gz \
>            && rm -r /usr/src/redis \
>            && apt-get purge -y --auto-remove $buildDeps
- 构建，Dockerfile 文件所在目录执行
> docker build -t [image_name:tag] [上下文路径/URL/-/.]

* 其他指令
> * COPY 文件复制: COPY [--chown=\<user>:\<group>] ["<源路径1::支持通配符>",... "<目标路径>"]

> * ADD 高级文件复制: ADD 使用方法与COPY类似，但是包含下载（若源路径是url，下载文件权限:600）、tar自动解压缩(若源文件是压缩包)

> * CMD 容器启动命令: CMD \<命令> | ["可执行文件","参数1","参数2"...] | ["参数1","参数2"...]    
  示例：CMD ["nginx", "-g", "daemon off;"] ，可在docker run的时候进行修改

> * ENTRYPOINT 入口点: ENTRYPOINT  <ENTRYPOINT> "<CMD>" 
  让镜像变成像可带参数的命令一样使用；应用运行前的准备工作；
  
> * ENV 设置环境变量: ENV <key> <value> | ENV <key1>=<value1> <key2>=<value2>...    
  支持环境变量展开: ADD、COPY、ENV、EXPOSE、LABEL、USER、WORKDIR、VOLUME、STOPSIGNAL、ONBUILD
  
> * ARG 构建参数: ARG <参数名>[=<默认值>]     
  ARG 所设置的构建环境的环境变量，在将来容器运行时是不会存在这些环境变量的。但是不要因此就使用 ARG 保存密码之类的信息，因为 docker history 还是可以看到所有值的。 
  Dockerfile 中的 ARG 指令是定义参数名称，以及定义其默认值。该默认值可以在构建命令 docker build 中用 --build-arg <参数名>=<值> 来覆盖。
  
> * VOLUME 定义匿名卷:VOLUME ["<路径1>", "<路径2>"...] | VOLUME <路径>
  事先指定某些目录挂载为匿名卷，这样在运行时如果用户不指定挂载，其应用也可以正常运行，不会向容器存储层写入大量数据。    
  docker run -d -v mydata:/data xxxx
  
> * EXPOSE 声明端口:EXPOSE <端口1> [<端口2>...]  并不会因为这个声明应用就会开启这个端口的服务

> * WORKDIR 指定工作目录: WORKDIR <工作目录路径>  改变以后各层的工作目录的位置
  
> * USER 指定当前用户:USER <用户名>[:<用户组>] 切换到指定的已存在用户

> * HEALTHCHECK 健康检查:HEALTHCHECK [--interval=<间隔30s>] [--timeout=<时长30s>] [--retries=<次数3>] CMD <命令> | HEALTHCHECK NONE    
     命令的返回值:0：成功；1：失败；2：保留
     
> * 特殊的指令: ONBUILD <其它指令>     
    这些指令，在当前镜像构建时并不会被执行。只有当以当前镜像为基础镜像，去构建下一级镜像的时候才会被执行。
   
  
***注意事项***:
* 每一个指令都会建立一层，指令执行结束后，commit这一层的修改，构成新的镜像
* Union FS 是有最大层数限制的，所以要精心定义每一层该如何构建
* 每一层构建的最后一定要清理掉无关文件
* 容器中的应用都应该以前台执行,容器内没有后台服务的概念
* Swarm mode 或 Kubernetes

二、容器
----------
1、启动/重启/暂停
>   docker run \<image_name:tag> \<cmd> | docker container start/restart/stop <id>

2、进入容器
> docker attach \<id> | docker exec -it \<id> \<cmd> ,前者exit会导致容器终止
     
3、删除终止状态容器
> docker container prune | docker container rm [-f] \<id>

4、查看容器输出
> docker logs <id>

***注意事项***:    
* 
* 

三、创建仓库
--------------
1、普通私有仓库
docker run -d -p 5000:5000 --restart=always --name registry -v /opt/data/registry:/var/lib/registry registry

2、Nexus

四、openssl 自行签发HTTPS证书
------------
1、创建 CA 私钥   
>        openssl genrsa -out "root-ca.key" 4096  

2、用私钥创建 CA 根证书请求文件
>        openssl req -new -key "root-ca.key" -out "root-ca.csr" -sha256 -subj '/C=<国家代码>/ST=<省份>/L=<市区>/O=<组织名>/CN=<通用名>'

3、配置 CA 根证书，新建 root-ca.cnf
>      [root_ca]
>      basicConstraints = critical,CA:TRUE,pathlen:1
>      keyUsage = critical, nonRepudiation, cRLSign, keyCertSign
>      subjectKeyIdentifier=hash

4、签发根证书
>     openssl x509 -req  -days 3650  -in "root-ca.csr" -signkey "root-ca.key" -sha256 -out "root-ca.crt" -extfile "root-ca.cnf" -extensions root_ca

5、生成站点 SSL 私钥
>     openssl genrsa -out "docker.domain.com.key" 4096

6、用私钥生成证书请求文件。
>     openssl req -new -key "docker.domain.com.key" -out "site.csr" -sha256 -subj '/C=CN/ST=Shanxi/L=Datong/O=Your Company Name/CN=docker.domain.com'

7、配置证书，新建 site.cnf 文件。
>     [server]
>     authorityKeyIdentifier=keyid,issuer
>     basicConstraints = critical,CA:FALSE
>     extendedKeyUsage=serverAuth
>     keyUsage = critical, digitalSignature, keyEncipherment
>     subjectAltName = DNS:docker.domain.com, IP:127.0.0.1
>     subjectKeyIdentifier=hash

8、签署站点 SSL 证书。
>     openssl x509 -req -days 750 -in "site.csr" -sha256 -CA "root-ca.crt" -CAkey "root-ca.key"  -CAcreateserial -out "docker.domain.com.crt" -extfile "site.cnf" -extensions server

这样已经拥有了 docker.domain.com 的网站 SSL 私钥 docker.domain.com.key 和 SSL 证书 docker.domain.com.crt 及 CA 根证书 root-ca.crt。

五、数据卷
----------
1、创建一个数据卷
> docker volume create \<name>

2、查看所有的数据卷
> docker volume ls

3、查看指定数据卷的信息
> docker volume inspect \<name>

六、网络
---------------
1、查看映射端口配置
> docker port \<container name> \<container port>

2、创建一个新的 Docker 网络
> docker network create -d (bridge|overlay) \<name>

七、docker-compose
----------------
1、安装
> sudo curl -L https://github.com/docker/compose/releases/download/1.17.1/docker-compose-\`uname -s\`-\`uname -m\` > /usr/local/bin/docker-compose

2、Compose 命令说明
>     docker-compose [-f config_file] [-p project_name] [--x-networking] [--x-network-driver bridge|... ::driver_name] [--verbose ::debug_info] [-v ::version] [COMMAND] [ARGS...]
*COMMAND*
* build 构建（重新构建）项目中的服务容器
> docker-compose build [--force-rm ::删除临时容器] [--no-cache ::不使用缓存] [--pull ::获取最新镜像] [SERVICE...]
* config  验证 Compose 文件格式是否正确
* down  停止 up 命令所启动的容器，并移除网络
* exec  进入指定的容器
* help  获得一个命令的帮助
* images  列出 Compose 文件中包含的镜像。
* kill 发送 SIGKILL 信号来强制停止服务容器
> docker-compose kill [-s SIGINT|SIG...] [SERVICE...]
* logs 查看服务容器的输出
> docker-compose logs [--no-color] [SERVICE...]
* pause 暂停一个服务容器
* port 打印某个容器端口所映射的公共端口
> docker-compose port [--protocol=tcp|udp] [--index=1|...] SERVICE PRIVATE_PORT
* ps 列出项目中目前的所有容器
> docker-compose ps [-q ::only ID] [SERVICE...]
* pull 拉取服务依赖的镜像
> docker-compose pull [--ignore-pull-failures] [SERVICE...]
* push 推送服务依赖的镜像到 Docker 镜像仓库
* restart 重启项目中的服务
> docker-compose restart [-t 10|... ::timeout] [SERVICE...]
* rm 删除所有（停止状态的）服务容器
> docker-compose rm [-f ::强制删除] [-v ::删除挂载数据卷] [SERVICE...]
* run 指定服务上执行一个命令
> docker-compose run [options] [-p PORT...] [-e KEY=VAL...] SERVICE [COMMAND] [ARGS...]
* scale 服务运行的容器个数
> docker-compose scale [options] [SERVICE=NUM...]
* start 启动已经存在的服务容器
* stop 停止已经处于运行状态的容器
* top 查看各个服务容器内运行的进程
* unpause 恢复处于暂停状态中的服务
* up 自动完成包括构建镜像，（重新）创建服务，启动服务，并关联服务相关容器的一系列操作
> docker-compose up [-d ::后台运行] [--no-color] [--force-recreate] [--no-recreate] [--no-build] [-t] [SERVICE...]
* version 打印版本信息

3、Compose 模板文件（docker-compose.yml）    

***注意事项***
* 每个服务都必须通过 image 指令指定镜像或 build 指令（需要 Dockerfile）等来自动构建生成镜像
* 

八、Docker Machine 
-------------------
1、安装
> sudo curl -L https://github.com/docker/machine/releases/download/v0.13.0/docker-machine-\`uname -s\`-\`uname -m\` > /usr/local/bin/docker-machine 



注意事项
--------------
1. 在 Ubuntu/Debian 上有 UnionFS，而对于 CentOS/RHEL 的用户来说，在没有办法使用 UnionFS(如aufs、overlay2) 的情况下，一定要配置 direct-lvm 给 devicemapper，无论是为了性能、稳定性还是空间利用率。
2. 

参考文献
---------------
1、[Docker — 从入门到实践](https://docker_practice.gitee.io/security/) 
