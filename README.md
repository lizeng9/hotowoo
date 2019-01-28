# Hotowoo

# 民宿


# 全局API接口规范
1. 书写采用下划线格式,示范: api_hello_world
2. GET查询采用传统参数：示范: ?author_id=1&status=1
   POST,PUT采用json 传递参数,客户端需留意.
3.  api 格式标准：
```
以book为例
新建:　
POST /api/v1/book/
{"name":"abc","year":2015}

修改:
PUT /api/v1/book/1
{"name":"abc","year":2015}

获取　
GET /api/v1/book/1

删除　
DELETE /api/v1/book/1

获取书籍列表
GET /api/v1/book?name=abc&orderby=created&order=asc&page=1&page_size=10
```


4. 查询通用参数
orderby: 排序字段
order: asc或desc
page: 页码
page_size: 每页大小

# 返回格式
### 正确返回格式
```
{
  "code": 0,
  "msg": "ok",
  "data": [], (可选) 数据列表
  "pagination":{ (可选) 分页相关数据
     "total_page":10, 总页数
     "total_record":100, 记录总数
     "page":1,  当前页
     "page_size", 每页记录数,
     "more": 0,  0/1 是否还有更多数据
   }
}
```
### 错误返回格式
code > 0 表示错误
code=401,表示未登录
code=1, 普通错误
msg,错误信息,客户端视情况提示用户.

```
{
  "code": 1,
  "msg": "ok",
}
```

# 提示
## 稀饭数据库配置
修改文件 
vendor/laravel/lumen-framework/config/database.php
```
        'mysql_tour' => [
            'driver'    => 'mysql',
            'host'      => env('DB_TOUR_HOST', 'localhost'),
            'port'      => env('DB_TOUR_PORT', 3306),
            'database'  => env('DB_TOUR_DATABASE', 'forge'),
            'username'  => env('DB_TOUR_USERNAME', 'forge'),
            'password'  => env('DB_TOUR_PASSWORD', ''),
            'charset'   => env('DB_CHARSET', 'utf8'),
            'collation' => env('DB_COLLATION', 'utf8_unicode_ci'),
            'prefix'    => env('DB_TOUR_PREFIX', ''),
            'timezone'  => env('DB_TIMEZONE', '+00:00'),
            'strict'    => env('DB_STRICT_MODE', false),
        ],

```
