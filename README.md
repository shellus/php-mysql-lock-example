# php & mysql 加锁的实例

使用pdo编写。演示了转账过程中的加锁操作。

### 使用方法

1. 修改`example.php`中的mysql连接账号密码数据库
2. 访问`example.php?reset=1` 以创建表结构和初始数据
3. 访问`example.php?ts=0` 将产生一次随机的转账。如果转账账户余额不足，将会失败，如果余额足够，将减少转账账户的余额，增加被转账账户的余额。无论转账成功还是失败，transfer表都会添加一条转账记录。 
4. 访问`example.php?ts=1` 同上，但是使用了事务和锁来保证转账过程中，其他人无法操作当前参与转账的两个账户的信息（其他人将会等待这两个账户的事务结束后才能读取这两个账户的信息）
5. 使用ab工具测试大量并发请求下，安全和非安全模式大量并发转账请求后，仓库总额是否为初始金额。建议的ab命令行参数为:`ab -n 1000 -c 20 http://localhost/php-mysql-lock-example/example.php?ts=1` 或`0`
6. 访问`example.php?info=1` 查看仓库剩余信息 

### 测试信息
作者在自己电脑的Ubuntu虚拟机中测试结果如下：

```$xslt

shellus@ubuntu:/data/www/php-mysql-lock-example$ curl localhost/php-mysql-lock-example/example.php?reset=1
array(3) {
  [0]=>
  string(6) "小明"
  [1]=>
  string(6) "小红"
  [2]=>
  string(6) "小白"
}
表和数据重置完成<br>
shellus@ubuntu:/data/www/php-mysql-lock-example$ curl localhost/php-mysql-lock-example/example.php?info=1
<ul><br>
<li>用户：小明   资金：10000.00</li><br>
<li>用户：小红   资金：10000.00</li><br>
<li>用户：小白   资金：10000.00</li><br>
</ul><br>
仓库共剩！30000.00<br>
shellus@ubuntu:/data/www/php-mysql-lock-example$ ab -n 1000 -c 50 http://localhost//php-mysql-lock-example/example.php?ts=1
This is ApacheBench, Version 2.3 <$Revision: 1706008 $>
Copyright 1996 Adam Twiss, Zeus Technology Ltd, http://www.zeustech.net/
Licensed to The Apache Software Foundation, http://www.apache.org/

Benchmarking localhost (be patient)
Completed 100 requests
Completed 200 requests
Completed 300 requests
Completed 400 requests
Completed 500 requests
Completed 600 requests
Completed 700 requests
Completed 800 requests
Completed 900 requests
Completed 1000 requests
Finished 1000 requests


Server Software:        nginx/1.10.0
Server Hostname:        localhost
Server Port:            80

Document Path:          //php-mysql-lock-example/example.php?ts=1
Document Length:        151 bytes

Concurrency Level:      50
Time taken for tests:   1.788 seconds
Complete requests:      1000
Failed requests:        952
   (Connect: 0, Receive: 0, Length: 952, Exceptions: 0)
Non-2xx responses:      53
Total transferred:      206362 bytes
HTML transferred:       59991 bytes
Requests per second:    559.43 [#/sec] (mean)
Time per request:       89.376 [ms] (mean)
Time per request:       1.788 [ms] (mean, across all concurrent requests)
Transfer rate:          112.74 [Kbytes/sec] received

Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:        0    0   0.5      0       4
Processing:    12   88  15.3     87     127
Waiting:       12   87  15.3     87     127
Total:         16   88  15.2     87     127

Percentage of the requests served within a certain time (ms)
  50%     87
  66%     94
  75%     96
  80%     98
  90%    108
  95%    118
  98%    123
  99%    124
 100%    127 (longest request)
shellus@ubuntu:/data/www/php-mysql-lock-example$ curl localhost/php-mysql-lock-example/example.php?info=1
<ul><br>
<li>用户：小明   资金：12753.00</li><br>
<li>用户：小红   资金：9321.00</li><br>
<li>用户：小白   资金：7926.00</li><br>
</ul><br>
仓库共剩！30000.00<br>
shellus@ubuntu:/data/www/php-mysql-lock-example$ ab -n 1000 -c 50 http://localhost//php-mysql-lock-example/example.php?ts=0
This is ApacheBench, Version 2.3 <$Revision: 1706008 $>
Copyright 1996 Adam Twiss, Zeus Technology Ltd, http://www.zeustech.net/
Licensed to The Apache Software Foundation, http://www.apache.org/

Benchmarking localhost (be patient)
Completed 100 requests
Completed 200 requests
Completed 300 requests
Completed 400 requests
Completed 500 requests
Completed 600 requests
Completed 700 requests
Completed 800 requests
Completed 900 requests
Completed 1000 requests
Finished 1000 requests


Server Software:        nginx/1.10.0
Server Hostname:        localhost
Server Port:            80

Document Path:          //php-mysql-lock-example/example.php?ts=0
Document Length:        55 bytes

Concurrency Level:      50
Time taken for tests:   1.958 seconds
Complete requests:      1000
Failed requests:        103
   (Connect: 0, Receive: 0, Length: 103, Exceptions: 0)
Total transferred:      200919 bytes
HTML transferred:       54919 bytes
Requests per second:    510.81 [#/sec] (mean)
Time per request:       97.884 [ms] (mean)
Time per request:       1.958 [ms] (mean, across all concurrent requests)
Transfer rate:          100.23 [Kbytes/sec] received

Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:        0    0   0.7      0       7
Processing:    12   96  16.4     97     131
Waiting:       12   96  16.4     97     131
Total:         15   96  16.2     98     131

Percentage of the requests served within a certain time (ms)
  50%     98
  66%    105
  75%    108
  80%    110
  90%    116
  95%    119
  98%    122
  99%    126
 100%    131 (longest request)
shellus@ubuntu:/data/www/php-mysql-lock-example$ curl localhost/php-mysql-lock-example/example.php?info=1
<ul><br>
<li>用户：小明   资金：13375.00</li><br>
<li>用户：小红   资金：10557.00</li><br>
<li>用户：小白   资金：11101.00</li><br>
</ul><br>
仓库共剩！35033.00<br>


```