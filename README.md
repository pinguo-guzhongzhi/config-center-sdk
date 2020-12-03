# config-center-sdk

micro 配置中心 PHP SDK

## test

```bash
bin/pg-config-dog  config:list clientId sec
```


## 使用方法

compose.json中添加如下配置

```json

{
    "repositories":{
        "pinguo-guzhongzhi":{
            "url":"https://github.com/pinguo-guzhongzhi/config-center-sdk.git",
            "type":"vcs"
        }
    },
    "require":{
        "pinguo-guzhongzhi/config-center-sdk":"^1.0"
    }
}

```

然后执行 

```bash
composer install
```